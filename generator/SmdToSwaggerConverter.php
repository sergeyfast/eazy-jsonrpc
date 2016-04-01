<?php
    // SMD to Swagger Converter
    // ======

    // You can overwrite swagger data by creating config_<hostname>.json
    //
    // Example given:
    //      {
    //      "securityDefinitions": {
    //          "auth": {
    //            "type": "oauth2",
    //            "flow": "accessCode",
    //            "authorizationUrl": "http://myshows.devel/oauth/authorize",
    //            "tokenUrl": "http://myshows.devel/oauth/token",
    //            "scopes": {
    //                  "basic": "All private profile methods"
    //            }
    //          }
    //        }
    //      }
    //
    // Also we supporting @scope <name> & @code <code> <message> tags from PHP annotation.
    // Scope adds 401 Unauthorized error.

    
    if ( empty( $argv[1] ) || empty( $argv[3] ) ) {
        printf( 'Usage: %s <smd-file|url> <hostname> <swagger.json>' . PHP_EOL, $argv[0] );
        die();
    }

    $output   = $argv[3];
    $hostname = $argv[2];
    $url      = $argv[1];
    $smd      = json_decode( file_get_contents( $url ) );
    if ( $smd === null ) {
        die( "Couldn't parse SMD file or URL! " . PHP_EOL );
    }

    $generator = ( new SmdToSwaggerConverter() )->Generate( $smd, $output, $hostname );


    /**
     * Class SmdToSwaggerConverter
     */
    class SmdToSwaggerConverter {

        /**
         * @var array
         */
        private $swagger = [ ];

        /**
         * @var array
         */
        private static $baseJsonRpcRequest = [
            'jsonrpc' => [ 'type' => 'string', 'default' => '2.0' ],
            'method'  => [ 'type' => 'string' ],
            'params'  => [ 'type' => 'object' ],
            'id'      => [ 'type' => 'integer', 'default' => 1 ],
        ];

        /**
         * @var array
         */
        private static $baseJsonRpcResponse = [
            'jsonrpc' => [ 'type' => 'string', 'default' => '2.0' ],
            'result'  => [ 'type' => 'object' ],
            'id'      => [ 'type' => 'integer', 'default' => 1 ],
        ];

        /**
         * @var array
         */
        private static $baseJsonRpcError = [
            'jsonrpc' => [ 'type' => 'string', 'default' => '2.0' ],
            'error'   => [ '$ref' => '#/definitions/JsonRpcErrorInner' ],
            'id'      => [ 'type' => 'integer', 'default' => 1 ],
        ];

        /**
         * @var array
         */
        private static $baseJsonRpcErrorInner = [
            'code'    => [ 'type' => 'integer' ],
            'message' => [ 'type' => 'string' ],
            'data'    => [ 'type' => 'object' ],
        ];


        /***
         * @param Object $smd
         * @param string $target   filename
         * @param string $hostname http://eazyjsonrpc/
         * @return int
         */
        public function Generate( $smd, $target, $hostname ) {
            $this->swagger = [
                'swagger'     => '2.0',
                'info'        => [
                    'title'   => $smd->description,
                    'version' => '1.0.0',
                ],
                'host'        => $hostname ?: '',
                'basePath'    => $smd->target,
                'schemes'     => [ 'http', 'https' ],
                'consumes'    => [ $smd->contentType ],
                'produces'    => [ $smd->contentType ],
                'paths'       => [ ],
                'definitions' => [
                    'JsonRpcErrorInner' => [
                        'properties' => self::$baseJsonRpcErrorInner,
                        'required'   => [ 'code', 'message' ],
                    ],
                    'JsonRpcError'      => [
                        'properties' => self::$baseJsonRpcError,
                        'required'   => [ 'jsonrpc', 'error', 'id' ],
                    ],
                ],
            ];

            $configFileName = __DIR__ . '/config_' . $hostname . '.json';
            if ( file_exists( $configFileName ) ) {
                $config        = file_get_contents( $configFileName );
                $config        = json_decode( $config, true );
                $this->swagger = array_merge( $this->swagger, $config );
            }

            foreach ( $smd->services as $name => $service ) {
                $this->generateService( $name, $service );
            }

            return file_put_contents( $target, json_encode( $this->swagger, JSON_PRETTY_PRINT ) );
        }


        /**
         * @param string $name
         * @param Object $service
         */
        private function generateService( $name, $service ) {
            $methodInfo  = explode( '.', $name, 2 );
            $namespace   = count( $methodInfo ) == 2 ? $methodInfo[0] : '';
            $method      = $namespace ? $methodInfo[1] : $name;
            $pathKey     = '/' . ( $namespace ? $namespace . '/' : '' ) . $method;
            $requestRef  = $name . '_Request';
            $responseRef = $name . '_Response';

            //main service params
            $swaggerService = [
                'tags'        => [ $namespace ?: 'public' ],
                'summary'     => $service->description,
                'description' => '',
                'parameters'  => [
                    [
                        'name'     => 'params',
                        'in'       => 'body',
                        'required' => true,
                        'schema'   => [ '$ref' => '#/definitions/' . $requestRef, ],
                    ],
                ],
                'responses'   => [
                    200       => [
                        'description' => 'json-rpc 2.0 response',
                        'schema'      => [ '$ref' => '#/definitions/' . $responseRef, ],
                    ],
                    'default' => [
                        'description' => 'json-rpc 2.0 error',
                        'schema'      => [ '$ref' => '#/definitions/JsonRpcError', ],
                    ],
                ],
            ];

            //auth scope
            if ( !empty ( $service->scope ) ) {
                $swaggerService['security']                      = [ [ 'auth' => explode( ',', $service->scope ) ] ];
                $swaggerService['responses'][401]['description'] = 'Unauthorized';
            }

            //building request
            $request                      = self::$baseJsonRpcRequest;
            $request['method']['default'] = ( $namespace ? $namespace . '.' : '' ) . $method;

            //building base request
            $this->swagger['definitions'][$requestRef]['properties'] = $request;
            $this->swagger['definitions'][$requestRef]['required']   = [ 'jsonrpc', 'method', 'id' ];

            //building base response
            $this->swagger['definitions'][$responseRef]['properties'] = self::$baseJsonRpcResponse;
            $this->swagger['definitions'][$responseRef]['required']   = [ 'jsonrpc', 'result', 'id' ];

            //building service errors
            if ( !empty( $service->errors ) ) {
                foreach ( $service->errors as $code => $message ) {
                    $swaggerService['responses'][$code]['description'] = $message;
                }
            }

            //inject request
            if ( $service->parameters ) {
                foreach ( $service->parameters as $k => $parameter ) {
                    $this->injectRequest( $parameter, $requestRef );
                }
            }

            //inject response
            $this->injectResponse( $service->returns, $responseRef );

            //register service
            $this->swagger['paths'][$pathKey]['post'] = $swaggerService;
        }


        /**
         * parse smd parameter to swagger
         * @param string $ref
         * @param Object $parameter
         */
        private function parseParameter( $ref, $parameter ) {
            $type      = !empty( $parameter->type ) ? $parameter->type : 'string';
            $name      = !empty( $parameter->name ) ? $parameter->name : '';
            $list_type = '';
            $required  = [ ];

            if ( substr( $type, strlen( $type ) - 2, 2 ) === '[]' ) {
                $list_type = substr( $type, 0, strlen( $type ) - 2 );
                $type      = 'array';
            }

            if ( $type === 'array' && !$list_type ) {
                $list_type = 'string';
            }

            $parameterParsed = array_filter( [
                'type'    => $type,
                'default' => !empty( $parameter->default ) ? $parameter->default : null,
            ] );

            if ( $list_type ) {
                $parameterParsed['items'] = [ 'type' => $list_type ];
            }

            if ( !$parameter->optional ) {
                $required[] = $name;
            }

            $this->swagger['definitions'][$ref]['properties'][$name] = $parameterParsed;
            if ( $required ) {
                $this->swagger['definitions'][$ref]['required'] = $required;
            }
        }


        /**
         * Inject Param to this.swagger.definitions[response|responseRef]
         * @param $parameter
         * @param $ref
         */
        private function injectRequest( $parameter, $ref ) {
            $refBody  = $ref . 'Body';
            $required = null;

            if ( !empty( $parameter->definitions ) ) {
                foreach ( $parameter->definitions as $name => $d ) {
                    $this->swagger['definitions'][$name] = $d;
                }
                unset( $parameter->definitions );
            }

            $name = $parameter->name;
            if ( !$parameter->optional ) {
                $required = $name;
            }

            unset( $parameter->name, $parameter->optional );

            $this->swagger['definitions'][$ref]['properties']['params'] = [ '$ref' => '#/definitions/' . $refBody ];
            if ( $parameter->type === 'object' ) {
                $refObject = $refBody . '_' . $name;

                $this->swagger['definitions'][$refBody]['properties'][$name] = [ '$ref' => '#/definitions/' . $refObject ];
                $this->swagger['definitions'][$refObject]                    = $parameter;
            } else {
                $this->swagger['definitions'][$refBody]['properties'][$name] = $parameter;
            }

            if ( $required ) {
                $this->swagger['definitions'][$refBody]['required'][] = $required;
                if ( !in_array( 'params', $this->swagger['definitions'][$ref]['required'], true ) ) {
                    $this->swagger['definitions'][$ref]['required'][] = 'params';
                }
            }
        }


        /**
         * Inject Param to this.swagger.definitions[response|responseRef]
         * @param $parameter
         * @param $ref
         */
        private function injectResponse( $parameter, $ref ) {
            $refBody = $ref . 'Body';
            if ( !empty( $parameter->definitions ) ) {
                foreach ( $parameter->definitions as $name => $d ) {
                    $this->swagger['definitions'][$name] = $d;
                }
                unset( $parameter->definitions );
            }

            if ( $parameter->type === 'object' ) {
                $this->swagger['definitions'][$refBody]['properties']       = $parameter->properties;
                $this->swagger['definitions'][$ref]['properties']['result'] = [ '$ref' => '#/definitions/' . $refBody ];
            } else {
                $this->swagger['definitions'][$ref]['properties']['result'] = $parameter;
            }
        }
    }