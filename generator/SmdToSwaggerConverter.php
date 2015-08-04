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
                '$ref' => 'JsonRpcErrorInner', //TODO should be "#/definitions/JsonRpcErrorInner"
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
            $methodInfo     = explode( '.', $name, 2 );
            $namespace      = count( $methodInfo ) == 2 ? $methodInfo[0] : '';
            $method         = $namespace ? $methodInfo[1] : $name;
            $pathKey        = '/' . ( $namespace ? $namespace . '/' : '' ) . $method;
            $requestRef     = $name . "Request";
            $responseRef    = $name . "Response";
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

            if ( !$service->parameters ) {
                $swaggerService['parameters'][0]['schema'] = array( 'type' => 'string' );
            }

            //building request
            foreach ( $service->parameters as $parameter ) {
                $this->parseParameter( $requestRef, $parameter );
            }

            //building response
            $this->swagger['definitions'][$responseRef]['properties'] = $this->baseJsonRpcResponse;
            $this->parseParameter( $responseRef, $service->returns, 'result' );

            //register service
            $this->swagger['paths'][$pathKey]['post'] = $swaggerService;
        }


        /**
         * parse smd parameter to swagger
         * @param string $ref
         * @param Object $parameter
         * @param string $name
         */
        private function parseParameter( $ref, $parameter, $name = null ) {
            $type      = !empty( $parameter->type ) ? $parameter->type : 'string';
            $list_type = '';

            if ( $name === null && !empty( $parameter->name ) ) {
                $name = $parameter->name;
            }

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

            $this->swagger['definitions'][$ref]['properties'][$name] = $parameterParsed;
        }
    }