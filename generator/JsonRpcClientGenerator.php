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
        public function __construct( $url, $smd, $className ) {
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
     * {$description}
     * @author JsonRpcClientGenerator
     * @date {$date}
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
        public function getMethod( $methodName, $methodData ) {
            $newDocLine    = PHP_EOL . str_repeat( ' ', 9 ) . '*';
            $description   = !empty( $methodData['description'] ) ? $methodData['description'] : $methodName;
            $strDocParams  = '';
            $strParamsArr  = array();
            $callParamsArr = array();
            $methodName    = str_replace( '.', '_', $methodName );

            // Add Default Parameter = IsNotification
            $methodData['parameters'][] = array(
                'name'        => 'isNotification',
                'optional'    => 'true',
                'type'        => 'bool',
                'default'     => false,
                'description' => 'set to true if call is notification',
            );

            // params
            if ( !empty( $methodData['parameters'] ) ) {
                // Set Doc Params
                foreach ( $methodData['parameters'] as $param ) {
                    $name              = $param['name'];
                    $strParam          = '$' . $name;
                    $strDocParamsArr   = array( $newDocLine );
                    $strDocParamsArr[] = '@param';

                    if ( !empty( $param['type'] ) ) {
                        $strDocParamsArr[] = $param['type'];
                    }

                    $strDocParamsArr[] = '$' . $name;
                    if ( !empty( $param['optional'] ) ) {
                        $strDocParamsArr[] = '[optional]';
                    }

                    if ( !empty( $param['description'] ) ) {
                        $strDocParamsArr[] = $param['description'];
                    }

                    if ( array_key_exists( 'default', $param ) ) {
                        $strParam .= sprintf( ' = %s', var_export( $param['default'], true ) );
                    }

                    $strDocParams .= rtrim( implode( ' ', $strDocParamsArr ) );
                    $strParamsArr[]       = $strParam;
                    $callParamsArr[$name] = sprintf( "'%s' => $%s", $name, $name );
                }
            }

            $strParams = ' ' . trim(
                    str_replace(
                        array( "\n", ',)', 'array (' ),
                        array( '', ')', 'array(' ),
                        implode( ', ', $strParamsArr )
                    ), ', ' ) . ' ';

            unset( $callParamsArr['isNotification'] );

            // returns
            if ( !empty( $methodData['returns'] ) && !empty( $methodData['returns']['type'] ) && is_string( $methodData['returns']['type'] ) ) {
                $strDocParams .= $newDocLine . ' @return BaseJsonRpcCall (result: ' . $methodData['returns']['type'] . ')';
            }

            $callParamsStr = implode( ', ', $callParamsArr );
            if ( !empty( $callParamsStr ) ) {
                $callParamsStr = sprintf( ' %s ', $callParamsStr );
            }




            $result = <<<php
        /**
         * {$description}{$strDocParams}
         */
        public function {$methodName}({$strParams}) {
            return \$this->call( __FUNCTION__, array({$callParamsStr}), \$this->getRequestId( \$isNotification ) );
        }

php;
            return $result;

        }


        /**
         * Get Footer
         */
        private function getFooter() {
            $url     = $this->url;
            $urlInfo = parse_url( $url );
            if ( !empty( $urlInfo ) ) {
                $url = sprintf( '%s://%s%s', $urlInfo['scheme'], $urlInfo['host'], $this->smd['target'] );
            }

            $result = <<<php


        /**
         * Get Instance
         * @return {$this->className}
         */
        public static function GetInstance() {
            return new self( '{$url}' );
        }

    }
php;

            return $result;
        }


        /**
         * Save to File
         */
        public function Generate() {
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
        public function SaveToFile() {
            return file_put_contents( $this->className . '.php', $this->Generate() );
        }
    }


    $g = new JsonRpcClientGenerator( $url, $smd, $className );
    if ( $g->SaveToFile() ) {
        printf( 'Done!' );
    }
