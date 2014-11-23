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

    generate( $smd, $output, $hostname );

    /***
     * @param object $smd
     * @param string $target   filename
     * @param string $hostname http://eazyjsonrpc/
     * @return int
     */
    function generate( $smd, $target, $hostname ) {
        $swagger = array(
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
            'definitions' => array(),
        );

        foreach ( $smd->services as $name => $service ) {
            $methodInfo     = explode( '.', $name, 2 );
            $namespace      = count( $methodInfo ) == 2 ? $methodInfo[0] : '';
            $method         = $namespace ? $methodInfo[1] : $name;
            $requestRef     = $name . "Request";
            $swaggerService = array(
                'tags'        => array( $namespace ?: 'public' ),
                'summary'     => $service->description,
                'description' => '',
                'parameters'  => array(
                    array(
                        'name'     => 'params',
                        'in'       => 'body',
                        'required' => false,
                        'schema'   => array( '$ref' => $requestRef, 'type' => 'string', )
                    )
                ),
                'responses'   => array(
                    200 => array( 'description' => 'json-rpc 2.0 response' )
                )
            );

            if ( !$service->parameters ) {
                $swaggerService['parameters'][0]['schema'] = array( 'type' => 'string' );
            }


            $swaggerRequired = array();
            $swaggerParams   = array();
            $items           = array();
            foreach ( $service->parameters as $parameter ) {
                $type = !empty( $parameter->type ) ? $parameter->type : 'string';
                switch ( $type ) {
                    case 'int':
                        $type = 'integer';
                        break;
                    case 'bool':
                        $type = 'boolean';
                        break;
                    case 'int[]':
                        $type = 'array';
                        $items = 'integer';
                        break;
                    case 'string[]':
                        $type = 'array';
                        $items = 'string';
                        break;
                    default:
                        break;
                }

                $swaggerParams[$parameter->name] = array(
                    'type'        => $type,
                    'json'        => array( 'name' => $parameter->name ),
                    'default'     => !empty( $parameter->default ) ? $parameter->default : '',
                );

                if ( $items ) {
                    $swaggerParams[$parameter->name]['items'] = array( 'type' => $items );
                }

                if ( empty( $parameter->optional ) ) {
                    $swaggerRequired[] = $parameter->name;
                }
            }

            $pathKey = '/' . ( $namespace ? $namespace . '/' : '' ) . $method;
            $swagger['paths'][$pathKey]['post'] = $swaggerService;

            if ( $service->parameters ) {
                $swagger['definitions'][$requestRef]['properties'] = $swaggerParams;
            }

            if ( $swaggerRequired ) {
                $swagger['definitions'][$requestRef]['required'] = $swaggerRequired;
            }
        }

        return file_put_contents( $target, json_encode( $swagger ) );
    }