<?php

    namespace EazyJsonRpc;

    use JsonMapper;

    /**
     * Base JSON-RPC 2.0 Client
     * @package    Eaze
     * @subpackage Model
     * @author     Sergeyfast
     * @link       http://www.jsonrpc.org/specification
     */
    class BaseJsonRpcClient {

        /**
         * Use Objects in Result
         * @var bool
         */
        public $UseObjectsInResults = false;

        /**
         * Curl Options
         * @var array
         */
        public $CurlOptions = [
            CURLOPT_POST           => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER     => [ 'Content-Type: application/json' ],
        ];

        /**
         * Current Request id
         * @var int
         */
        private $id = 1;

        /**
         * Is Batch Call Flag
         * @var bool
         */
        private $isBatchCall = false;

        /**
         * Batch Calls
         * @var BaseJsonRpcCall[]
         */
        private $batchCalls = [];

        /**
         * Batch Notifications
         * @var BaseJsonRpcCall[]
         */
        private $batchNotifications = [];


        /**
         * Create New JsonRpc client
         * @param string $serverUrl
         */
        public function __construct( string $serverUrl ) {
            $this->CurlOptions[CURLOPT_URL] = $serverUrl;
        }


        /**
         * Get Next Request Id
         * @param bool $isNotification
         * @return int|null
         */
        protected function getRequestId( $isNotification = false ): ?int {
            return $isNotification ? null : $this->id++;
        }


        /**
         * Begin Batch Call
         * @return bool
         */
        public function BeginBatch(): bool {
            if ( !$this->isBatchCall ) {
                $this->batchNotifications = [];
                $this->batchCalls         = [];
                $this->isBatchCall        = true;
                return true;
            }

            return false;
        }


        /**
         * Commit Batch
         * @return array
         */
        public function CommitBatch(): array {
            if ( !$this->isBatchCall || ( !$this->batchCalls && !$this->batchNotifications ) ) {
                return [];
            }

            $this->processCalls( array_merge( $this->batchCalls, $this->batchNotifications ) );

            $result = [];
            foreach ( $this->batchCalls as $i => $call ) {
                if ( $call->HasError() ) {
                    $result[] = new BaseJsonRpcException( $call );
                } else {
                    $result[] = $call->Result;
                }
            }
            $this->RollbackBatch();

            return $result;
        }


        /**
         * Rollback Calls
         * @return bool
         */
        public function RollbackBatch(): bool {
            $this->isBatchCall = false;
            $this->batchCalls  = [];

            return true;
        }


        /**
         * Process Call
         * @param string $method
         * @param array  $parameters
         * @param null   $id
         * @param string $returnType
         * @return mixed
         * @throws BaseJsonRpcException
         * @throws \JsonMapper_Exception
         */
        protected function call( string $method, string $returnType, array $parameters = [], $id = null ) {
            $method = str_replace( '_', '.', $method );
            $call   = new BaseJsonRpcCall( $method, $parameters, $id );
            if ( $this->isBatchCall ) {
                if ( $call->Id ) {
                    $this->batchCalls[$call->Id] = $call;
                } else {
                    $this->batchNotifications[] = $call;
                }
            } else {
                $this->processCalls( [ $call ] );
            }

            if ( $call->HasError() ) {
                throw new BaseJsonRpcException( $call );
            }

            return $this->convertResult( $call, $returnType );
        }


        /**
         * Process Magic Call
         * @param string $method
         * @param array  $parameters
         * @return BaseJsonRpcCall
         * @throws BaseJsonRpcException
         * @throws \JsonMapper_Exception
         */
        public function __call( string $method, array $parameters = [] ) {
            return $this->call( $method, '', $parameters, $this->getRequestId() );
        }


        /**
         * Process Calls
         * @param BaseJsonRpcCall[] $calls
         * @return mixed
         * @throws HttpException
         */
        protected function processCalls( array $calls ): bool {
            // Prepare Data
            $singleCall = !$this->isBatchCall ? reset( $calls ) : null;
            $result     = $this->batchCalls ? array_values( array_map( '\EazyJsonRpc\BaseJsonRpcCall::GetCallData', $calls ) ) : BaseJsonRpcCall::GetCallData( $singleCall );

            // Send Curl Request
            $options = $this->CurlOptions + [ CURLOPT_POSTFIELDS => json_encode( $result ) ];
            $ch      = curl_init();
            curl_setopt_array( $ch, $options );

            $data = curl_exec( $ch );

            if ( curl_errno( $ch ) ) {
                throw new HttpException( 'Error with curl response: ' . curl_error( $ch ) );
            }

            $httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            if ( $httpCode != 200 ) {
                throw new HttpException( 'Error with http response, got http status: ' . $httpCode );
            }

            $data = json_decode( $data, !$this->UseObjectsInResults );
            curl_close( $ch );
            if ( $data === null ) {
                return false;
            }

            // Process Results for Batch Calls
            if ( $this->batchCalls ) {
                foreach ( $data as $dataCall ) {
                    // Problem place?
                    $key = $this->UseObjectsInResults ? $dataCall->id : $dataCall['id'];
                    $this->batchCalls[$key]->SetResult( $dataCall, $this->UseObjectsInResults );
                }
            } else {
                // Process Results for Call
                $singleCall->SetResult( $data, $this->UseObjectsInResults );
            }

            return true;
        }


        /**
         * Convert Result to concrete type
         * @param BaseJsonRpcCall $call
         * @param string          $returnType
         * @return array|bool|float|int|mixed|object|string
         * @throws \JsonMapper_Exception
         */
        private function convertResult( BaseJsonRpcCall $call, string $returnType ) {
            $result                  = null;
            $mapper                  = new JsonMapper();
            $mapper->bEnforceMapType = false;
            switch ( true ) {
                case substr( $returnType, -2 ) == '[]':
                    $result = $mapper->mapArray( $call->Result, [], rtrim( $returnType, '[]' ) );
                    break;
                case $returnType == 'mixed':
                case $returnType == 'array':
                    $result = [];
                    if ( $call->Result ) {
                        $result = $call->Result;
                    }
                    break;
                case $returnType == 'object':
                    $result = (object) [];
                    if ( $call->Result ) {
                        $result = $call->Result;
                    }
                    break;
                case $returnType == 'int':
                    $result = 0;
                    if ( $call->Result ) {
                        $result = (int) $call->Result;
                    }
                    break;
                case $returnType == 'float':
                    $result = 0;
                    if ( $call->Result ) {
                        $result = (float) $call->Result;
                    }
                    break;
                case $returnType == 'bool':
                    $result = false;
                    if ( $call->Result ) {
                        $result = (bool) $call->Result;
                    }
                    break;
                case $returnType == 'string':
                    $result = '';
                    if ( $call->Result ) {
                        $result = (string) $call->Result;
                    }
                    break;
                default:
                    $result = $mapper->map( $call->Result, new $returnType );
            }
            return $result;
        }
    }

