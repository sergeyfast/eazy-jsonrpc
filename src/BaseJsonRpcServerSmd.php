<?php

    namespace EazyJsonRpc;

    /**
     * JSON RPC SMD Generator for JSON-RPC
     *
     * @link       http://dojotoolkit.org/reference-guide/1.8/dojox/rpc/smd.html
     * @package    Eaze
     * @subpackage Model
     * @author     Sergeyfast
     */
    class BaseJsonRpcServerSmd {

        /**
         * @var array
         */
        protected $instances = [ ];

        /**
         * Hidden Methods
         * @var array
         */
        protected $hiddenMethods = [ ];

        /**
         * User Types
         * @var array
         */
        protected static $types = [ ];

        /**
         * Simple Types
         * @var array
         */
        protected static $simpleTypes = [ 'int', 'string', 'integer', 'bool', 'boolean', 'float', 'array' ];


        /**
         * Json schema type mapping
         * @var array
         */
        protected static $typeMappings = [
            'int'     => 'integer',
            'bool'    => 'boolean',
            'float'   => 'number',
            'int[]'   => 'integer[]',
            'bool[]'  => 'boolean[]',
            'float[]' => 'number[]',
        ];


        /**
         * BaseJsonRpcServerSmd constructor.
         * @param array $instances
         * @param array $hiddenMethods
         */
        public function __construct( array $instances, array $hiddenMethods ) {
            $this->instances     = $instances;
            $this->hiddenMethods = $hiddenMethods;
        }


        /**
         * Get Prop array
         * @param array $t
         * @return array
         */
        protected static function getProps( $t ) {
            $props = [ ];
            foreach ( $t['fields'] as $name => $f ) {
                if ( $f['isRef'] ) {
                    $props[$name] = [
                        'type' => $f['isArray'] ? 'array' : 'object',
                    ];

                    if ( $f['isArray'] ) {
                        $props[$name]['items'] = [ '$ref' => '#/definitions/' . $f['type'] ];
                    } else {
                        $props[$name]['$ref'] = '#/definitions/' . $f['type'];
                    }

                } else {
                    $props[$name] = array_filter( [
                        'type'  => $f['isArray'] ? 'array' : self::getJsonSchemaType( $f['type'] ),
                        'items' => $f['isArray'] ? [ 'type' => self::getJsonSchemaType( $f['type'] ) ] : null,
                    ] );
                }
            }
            return $props;
        }


        /**
         * Get Doc Comment
         * @param $comment
         * @return string|null
         */
        private static function getDocDescription( $comment ) {
            $result = null;
            if ( preg_match( '/\*\s+([^@]*)\s+/s', $comment, $matches ) ) {
                $result = str_replace( '*', "\n", trim( trim( $matches[1], '*' ) ) );
            }

            return $result;
        }


        /**
         * Get Doc Return Comment
         * @param string $comment
         * @param string $commentType return | var
         * @return array type description
         */
        private static function getDocVar( $comment, $commentType = 'return' ) {
            // set simple return type
            if ( preg_match( '/@' . $commentType . '\s+([^\s]+)\s*([^\n\*]+)/', $comment, $matches ) ) {
                return [ $matches[1], trim( $matches[2] ) ];
            }

            return [ null, null ];
        }


        /**
         * Get Service Map
         * Maybe not so good realization of auto-discover via doc blocks
         * @return array
         */
        public function GetServiceMap() {
            $result = [
                'transport'   => 'POST',
                'envelope'    => 'JSON-RPC-2.0',
                'SMDVersion'  => '2.0',
                'contentType' => 'application/json',
                'target'      => !empty( $_SERVER['REQUEST_URI'] ) ? substr( $_SERVER['REQUEST_URI'], 0, strpos( $_SERVER['REQUEST_URI'], '?' ) ) : '',
                'services'    => [ ],
                'description' => '',
            ];

            foreach ( $this->instances as $namespace => $instance ) {
                $rc = new \ReflectionClass( $instance );

                // Get Class Description
                if ( $rcDocComment = self::getDocDescription( $rc->getDocComment() ) ) {
                    $result['description'] .= $rcDocComment . PHP_EOL;
                }

                $methods = $rc->getMethods();
                foreach ( $rc->getTraits() as $t ) {
                    $methods = array_merge( $t->getMethods(), $methods );
                }

                foreach ( $methods as $method ) {
                    /** @var \ReflectionMethod $method */
                    if ( !$method->isPublic() || in_array( strtolower( $method->getName() ), $this->hiddenMethods, true ) ) {
                        continue;
                    }

                    $methodName = ( $namespace ? $namespace . '.' : '' ) . $method->getName();
                    $docComment = $method->getDocComment();

                    $result['services'][$methodName] = [ 'parameters' => [ ] ];

                    // set description
                    if ( $rmDocComment = self::getDocDescription( $docComment ) ) {
                        $result['services'][$methodName]['description'] = $rmDocComment;
                    }

                    // @param\s+([^\s]*)\s+([^\s]*)\s*([^\s\*]*)
                    $parsedParams = [ ];
                    if ( preg_match_all( '/@param\s+([^\s]*)\s+([^\s]*)\s*([^\n\*]*)/', $docComment, $matches ) ) {
                        foreach ( $matches[2] as $number => $name ) {
                            $type = $matches[1][$number];
                            $desc = $matches[3][$number];
                            $name = trim( $name, '$' );

                            $param               = self::getServiceMapReturnType( $type, $desc );
                            $parsedParams[$name] = array_filter( $param );
                        }
                    };

                    // process params
                    foreach ( $method->getParameters() as $parameter ) {
                        $name  = $parameter->getName();
                        $param = [ 'name' => $name, 'optional' => $parameter->isDefaultValueAvailable() ];
                        if ( array_key_exists( $name, $parsedParams ) ) {
                            $param += $parsedParams[$name];
                        }

                        if ( $param['optional'] && $parameter->getDefaultValue() !== null ) {
                            $param['default'] = $parameter->getDefaultValue();
                        }

                        $result['services'][$methodName]['parameters'][] = $param;
                    }

                    // set simple return type
                    list( $t, $c ) = self::getDocVar( $docComment );
                    if ( $t ) {
                        $result['services'][$methodName]['returns'] = self::getServiceMapReturnType( $t, $c );
                    }
                }
            }

            return $result;
        }


        /**
         * Get Json Schema Type from PHP Type
         * @param string $type
         * @return string
         */
        protected static function getJsonSchemaType( $type ) {
            // replace php type to json schema type
            if ( !empty( self::$typeMappings[$type] ) ) {
                return self::$typeMappings[$type];
            }

            return $type;
        }


        /**
         * @param string $type
         * @param        $description
         * @return array
         */
        protected static function getServiceMapReturnType( $type, $description ) {
            $sType   = rtrim( $type, '[]' );
            $isArray = $sType !== $type || $type === 'array';
            if ( in_array( $sType, self::$simpleTypes, true ) ) {
                $items = $type === 'array' ? [ 'type' => 'string' ] : [ 'type' => $sType ];
                if ( $isArray ) {
                    $type = 'array';
                }

                return array_filter( [
                    'type'        => self::getJsonSchemaType( $type ),
                    'description' => $description,
                    'items'       => $isArray ? $items : null,
                ] );
            }

            self::fillTypes( $sType );
            $t = self::$types[strtolower( $sType )];
            $r = [
                'type'        => $isArray ? 'array' : 'object',
                'description' => $t['description'],
                'properties'  => !$isArray ? self::getProps( $t ) : null,
                'definitions' => [ ],
            ];

            if ( $isArray ) {
                $r['items']                   = [ '$ref' => '#/definitions/' . $t['type'] ];
                $r['definitions'][$t['type']] = [ 'properties' => self::getProps( $t ), ];
            }

            foreach ( $t['fields'] as $fName => $fInfo ) {
                if ( !$fInfo['isRef'] ) {
                    continue;
                }

                $r['definitions'][$fInfo['type']] = [
                    'properties' => self::getProps( self::$types[strtolower( $fInfo['type'] )] ),
                ];
            }

            return array_filter( $r );
        }


        /**
         * @param string $type
         */
        private static function fillTypes( $type ) {
            if ( !empty( self::$types[strtolower( $type )] ) ) {
                return;
            }

            $rc = new \ReflectionClass( $type );
            $t  = [
                'description' => self::getDocDescription( $rc->getDocComment() ),
                'fields'      => [ ],
                'type'        => $type,
            ];

            $delayed = [ ];
            foreach ( $rc->getProperties( \ReflectionProperty::IS_PUBLIC ) as $r ) {
                list( $pt, $c ) = self::getDocVar( $r->getDocComment(), 'var' );
                $spt  = rtrim( $pt, '[]' );
                $info = [ 'type' => $spt, 'description' => $c, 'isRef' => false, 'isArray' => $spt !== $pt ];
                if ( !in_array( $spt, self::$simpleTypes, true ) ) {
                    $delayed[]     = $spt;
                    $info['isRef'] = true;
                }

                $t['fields'][$r->getName()] = $info;
            }

            // set type to global
            self::$types[strtolower( $type )] = $t;

            foreach ( $delayed as $t ) {
                self::fillTypes( $t );
            }
        }
    }