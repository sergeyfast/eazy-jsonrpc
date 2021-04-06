<?php
    /// JSON-RPC 2.0 Proxy Class Generator
    if ( empty( $argv[1] ) || empty( $argv[2] ) ) {
        printf( 'Usage: %s <smd-file|url> <ClassName>' . PHP_EOL, $argv[0] );
        die();
    }

    $className = $argv[2];
    $url       = $argv[1];
    $smd       = json_decode( file_get_contents( $url ), true );

    if ( $smd === null ) {
        die( "Couldn't parse SMD file or URL! " . PHP_EOL );
    }

    // go below

    /**
     * Json Rpc Client Generator
     */
    class JsonRpcClientGenerator {

        /** @var array */
        private $smd;

        /**@var string */
        private $className;

        /** @var string */
        private $url;

        /** @var string */
        private $result;


        /**
         * Constructor
         * @param string $url
         * @param array  $smd SMD Schema
         * @param string $className
         */
        public function __construct( string $url, array $smd, string $className ) {
            $this->url       = $url;
            $this->smd       = $smd;
            $this->className = $className;
        }


        /**
         * Get Header
         * @return string
         */
        private function getHeader() {
            $description = !empty( $this->smd['description'] ) ? $this->smd['description'] : $this->className;
            $description = str_replace( "\n", PHP_EOL . '     * ', $description );
            $date        = date( 'd.m.Y G:i' );
            $result      = <<<php
<?php
/**
* PHP RPC Client by JsonRpcClientGenerator
* @date {$date}
*/

namespace JsonRpcClient;

	use EazyJsonRpc\BaseJsonRpcClient;
    use EazyJsonRpc\BaseJsonRpcException;
    use EazyJsonRpc\HttpException;
    use JsonMapper_Exception;

    /**
     * {$description}
     */
    class {$this->className} extends BaseJsonRpcClient {
php;

            return $result;
        }


        /**
         * @param $methodName
         * @param $methodData
         * @return string
         */
        public function getMethod( $methodName, $methodData ): string {
            $newDocLine    = PHP_EOL . str_repeat( ' ', 8 ) . '*';
            $description   = sprintf( '<%s> RPC method', $methodName );
            $description   .= !empty( $methodData['description'] ) ? $newDocLine . ' ' . trim( $methodData['description'] ) : '';
            $description   = str_replace( "\n", PHP_EOL, $description );
            $strDocParams  = '';
            $strParamsArr  = [];
            $callParamsArr = [];
            $methodName    = str_replace( '.', '_', $methodName );

            // params
            if ( !empty( $methodData['parameters'] ) ) {
                // Set Doc Params
                foreach ( $methodData['parameters'] as $param ) {
                    $name        = $param['name'];
                    $strDocParam = $newDocLine;
                    $strDocParam .= " @param";
                    if ( !empty( $param['type'] ) ) {
                        $strDocParam .= " " . $this->getPhpType( $param['type'] );
                    }
                    $strParam      = $this->getPhpType( $param['type'] ) . ' $' . $name;
                    $optionalParam = !empty( $param['optional'] );
                    if ( $optionalParam ) {
                        $strDocParam .= '|null';
                    }

                    $strDocParam .= ' $' . $name;
                    if ( $optionalParam ) {
                        $strDocParam .= ' [optional]';
                    }

                    if ( !empty( $param['description'] ) ) {
                        $strDocParam .= " " . $param['description'];
                    }

                    if ( array_key_exists( 'default', $param ) ) {
                        $strParam .= sprintf( ' = %s', var_export( $param['default'], true ) );
                    } else {
                        if ( $optionalParam ) {
                            $strParam .= ' = null';
                        }
                    }

                    $strDocParams         .= rtrim( $strDocParam );
                    $strParamsArr[]       = $strParam;
                    $callParamsArr[$name] = sprintf( "'%s' => $%s", $name, $name );
                }
            }
            $strDocParams .= $newDocLine . ' @param bool $isNotification [optional] set to true if call is notification';

            $strParams = str_replace(
                [ "\n", ',)', 'array (' ],
                [ '', ')', 'array(' ],
                implode( ', ', $strParamsArr )
            );

            $strParams      .= ', $isNotification = false ';
            $strParams      = ' ' . trim( $strParams, ', ' ) . ' ';
            $returnType     = '';
            $optionalReturn = '';
            $strDocReturns  = $newDocLine . ' @return mixed';
            $strReturnType  = '';
            // returns
            if ( !empty( $methodData['returns'] ) ) {
                $strDocReturns = '';
                if ( !empty( $methodData['returns']['type'] ) && is_string( $methodData['returns']['type'] ) ) {
                    $returnType    = $this->getPhpType( $methodData['returns']['type'] );
                    $strDocReturns .= $newDocLine . ' @return ' . $returnType;
                }
                if ( !empty( $methodData['returns']['optional'] ) ) {
                    $optionalReturn = '?';
                    $strDocReturns  .= '|null';
                }
                if ( !empty( $methodData['returns']['description'] ) ) {
                    $strDocReturns .= ' ' . $methodData['returns']['description'];
                }
                if ( $returnType != 'mixed' ) {
                    $strReturnType = sprintf( ': %s%s', $optionalReturn, $returnType );
                }
            }
            $strDocParams  .= $strDocReturns;
            $callParamsStr = implode( ', ', $callParamsArr );
            if ( !empty( $callParamsStr ) ) {
                $callParamsStr = sprintf( ' %s ', $callParamsStr );
            }


            return <<<php
        /**
        * {$description}{$strDocParams}
        * @throws BaseJsonRpcException
        * @throws HttpException
        * @throws JsonMapper_Exception
        */
        public function {$methodName}({$strParams})$strReturnType {
            return \$this->call( __FUNCTION__, '$returnType', [{$callParamsStr}], \$this->getRequestId( \$isNotification ) );
        }

php;

        }


        /**
         * Get Footer
         */
        private function getFooter(): string {
            $rpcUrl  = $this->url;
            $urlInfo = parse_url( $rpcUrl );
            if ( !empty( $urlInfo ) ) {
                $rpcUrl = sprintf( '%s://%s%s', $urlInfo['scheme'], $urlInfo['host'], $this->smd['target'] );
            }

            return <<<php


        /**
         * Get Instance
         * @param \$url string
         * @return {$this->className}
         */
        public static function GetInstance( string \$url ): {$this->className} {
            return new self( \$url );
        }

    }
php;
        }


        /**
         * Return PHP type from SMD type
         * @param string $smdType
         * @return string
         */
        private function getPhpType( string $smdType ): string {
            switch ( $smdType ) {
                case "string":
                    return "string";
                case "object":
                case "array":
                    return "array";
                case "boolean":
                    return "bool";
                case "float":
                    return "float";
                case "integer":
                    return "int";
            }
            return "mixed";
        }


        /**
         * Save to File
         */
        public function Generate(): string {
            $this->result = $this->getHeader();

            foreach ( $this->smd['services'] as $methodName => $methodData ) {
                $this->result .= str_repeat( PHP_EOL, 2 );
                $this->result .= $this->getMethod( $methodName, $methodData );
            }

            $this->result .= $this->getFooter();

            return $this->result;
        }


        /**
         * Save To File
         * @return int
         */
        public function SaveToFile(): int {
            return file_put_contents( $this->className . '.php', $this->Generate() );
        }
    }


    $g = new JsonRpcClientGenerator( $url, $smd, $className );
    if ( $g->SaveToFile() ) {
        printf( 'Done!' );
    }
