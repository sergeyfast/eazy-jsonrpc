<?php
    /// SMD to Swagger Converter
    if ( empty( $argv[1] ) || empty( $argv[2] ) || empty( $argv[3] ) ) {
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

    $generator = new SmdToSwaggerConverter();
    $generator->generate( $smd, $output, $hostname );


    /**
     * Class SmdToSwaggerConverter
     */
    class SmdToSwaggerConverter {

        /**
         * @var array
         */
        private $swagger = array();

        /**
         * @var array
         */
        private $baseJsonRpcRequest = array(
            'jsonrpc' => array(
                'type'    => 'string',
                'default' => '2.0',
            ),
            'method'  => array(
                'type' => 'string',
            ),
            'params'  => array(
                'type' => 'object',
            ),
            'id'      => array(
                'type'    => 'integer',
                'default' => 1,
            ),
        );

        /**
         * @var array
         */
        private $baseJsonRpcResponse = array(
            'jsonrpc' => array(
                'type'    => 'string',
                'default' => '2.0',
            ),
            'result'  => array(
                'type' => 'object',
            ),
            'id'      => array(
                'type'    => 'integer',
                'default' => 1,
            ),
        );

        /**
         * @var array
         */
        private $baseJsonRpcError = array(
            'jsonrpc' => array(
                'type'    => 'string',
                'default' => '2.0',
            ),
            'error'   => array(
                '$ref' => '#/definitions/JsonRpcErrorInner',
            ),
            'id'      => array(
                'type'    => 'integer',
                'default' => 1,
            ),
        );

        /**
         * @var array
         */
        private $baseJsonRpcErrorInner = array(
            'code'    => array(
                'type' => 'integer',
            ),
            'message' => array(
                'type' => 'string',
            ),
            'data'    => array(
                'type' => 'object',
            ),
        );


        /***
         * @param Object $smd
         * @param string $target   filename
         * @param string $hostname http://eazyjsonrpc/
         * @return int
         */
        public function Generate( $smd, $target, $hostname ) {
            $this->swagger = array(
                'swagger'     => '2.0',
                'info'        => array(
                    'title'   => $smd->description,
                    'version' => '1.0.0'
                ),
                'host'        => $hostname,
                'basePath'    => $smd->target,
                'schemes'     => array( 'http', 'https' ),
                'consumes'    => array( $smd->contentType ),
                'produces'    => array( $smd->contentType ),
                'paths'       => array(),
                'definitions' => array(
                    'JsonRpcErrorInner' => array(
                        'properties' => $this->baseJsonRpcErrorInner,
                    ),
                    'JsonRpcError'      => array(
                        'properties' => $this->baseJsonRpcError,
                    ),
                ),
            );

            foreach ( $smd->services as $name => $service ) {
                $this->generateService( $name, $service );
            }

            return file_put_contents( $target, json_encode( $this->swagger ) );
        }


        /**
         * @param string $name
         * @param Object $service
         */
        private function generateService( $name, $service ) {
            $methodInfo      = explode( '.', $name, 2 );
            $namespace       = count( $methodInfo ) == 2 ? $methodInfo[0] : '';
            $method          = $namespace ? $methodInfo[1] : $name;
            $pathKey         = '/' . ( $namespace ? $namespace . '/' : '' ) . $method;
            $requestRef      = $name . "Request";
            $requestRefInner = $requestRef . "Inner";
            $responseRef     = $name . "Response";

            //main service params
            $swaggerService = array(
                'tags'        => array( $namespace ?: 'public' ),
                'summary'     => $service->description,
                'description' => '',
                'parameters'  => array(
                    array(
                        'name'     => 'params',
                        'in'       => 'body',
                        'required' => false,
                        'schema'   => array( '$ref' => '#/definitions/' . $requestRef, 'type' => 'string', )
                    )
                ),
                'responses'   => array(
                    200       => array(
                        'description' => 'json-rpc 2.0 response',
                        'schema'      => array( '$ref' => '#/definitions/' . $responseRef, )
                    ),
                    'default' => array(
                        'description' => 'json-rpc 2.0 error',
                        'schema'      => array( '$ref' => '#/definitions/JsonRpcError', )
                    )
                )
            );

            //building request
            $request                      = $this->baseJsonRpcRequest;
            $request['method']['default'] = $method;

            if ( $service->parameters ) {
                $request['params'] = array( '$ref' => '#/definitions/' . $requestRefInner );
                foreach ( $service->parameters as $parameter ) {
                    $this->parseParameter( $requestRefInner, $parameter, true );
                }
            }

            $this->swagger['definitions'][$requestRef]['properties'] = $request;

            //building response
            $this->swagger['definitions'][$responseRef]['properties'] = $this->baseJsonRpcResponse;
            $this->parseParameter( $responseRef, $service->returns, false );

            //register service
            $this->swagger['paths'][$pathKey]['post'] = $swaggerService;
        }


        /**
         * parse smd parameter to swagger
         * @param string $ref
         * @param Object $parameter
         * @param bool   $isRequest
         */
        private function parseParameter( $ref, $parameter, $isRequest ) {
            $type      = !empty( $parameter->type ) ? $parameter->type : 'string';
            $name      = !empty( $parameter->name ) ? $parameter->name : '';
            $list_type = '';

            if ( is_object( $type ) ) {
                //TODO implement
                return;
            }

            if ( substr( $type, strlen( $type ) - 2, 2 ) == '[]' ) {
                $list_type = substr( $type, 0, strlen( $type ) - 2 );
                $type      = 'array';
            }

            switch ( $type ) {
                case 'int':
                    $type = 'integer';
                    break;
                case 'bool':
                    $type = 'boolean';
                    break;
            }

            if ( $type == 'array' && !$list_type ) {
                $list_type = 'string';
            }

            $parameterParsed = array(
                'type'    => $type,
                'default' => !empty( $parameter->default ) ? $parameter->default : '',
            );

            if ( $list_type ) {
                $parameterParsed['items'] = array( 'type' => $list_type );
            }

            if ( $isRequest ) {
                $this->swagger['definitions'][$ref]['properties'][$name] = $parameterParsed;
            } else {
                $this->swagger['definitions'][$ref]['properties']['result'] = $parameterParsed;
            }
        }
    }