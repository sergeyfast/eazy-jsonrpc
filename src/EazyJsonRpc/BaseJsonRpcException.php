<?php

    namespace EazyJsonRpc;

    class BaseJsonRpcException extends \Exception {

        protected $data;


        public function __construct( BaseJsonRpcCall $baseJsonRpcCall, \Throwable $previous = null ) {
            if ( !$baseJsonRpcCall->HasError() ) {
                return;
            }
            $error = (array) $baseJsonRpcCall->Error;

            if ( isset( $error['data'] ) ) {
                $this->data = $error['data'];
            }

            $message = '';
            $code    = 0;
            if ( isset( $error['message'] ) ) {
                $message = $error['message'];
            }

            if ( isset( $error['code'] ) ) {
                $code = $error['code'];
            }

            parent::__construct( $message, $code, $previous );
        }


        /**
         * @return mixed
         */
        public function getData() {
            return $this->data;
        }

    }
