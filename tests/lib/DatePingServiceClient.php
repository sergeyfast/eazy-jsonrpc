<?php
    /**
     * Simple Date Time Service
     * Simple Echo Service
     * 
     * @author JsonRpcClientGenerator
     * @date 23.11.2014 16:00
     */
    class DatePingServiceClient extends BaseJsonRpcClient {

        /**
         * Get Current Time
         * @param string $timezone [optional]
         * @param string $format [optional]
         * @param bool $isNotification [optional] set to true if call is notification
         * @return BaseJsonRpcCall (result: string)
         */
        public function date_GetTime( $timezone = 'UTC', $format = 'c', $isNotification = false ) {
            return $this->call( __FUNCTION__, array( 'timezone' => $timezone, 'format' => $format ), $this->getRequestId( $isNotification ) );
        }


        /**
         * Returns associative array containing dst, offset and the timezone name
         * @param bool $isNotification [optional] set to true if call is notification
         * @return BaseJsonRpcCall (result: array)
         */
        public function date_GetTimeZones( $isNotification = false ) {
            return $this->call( __FUNCTION__, array(), $this->getRequestId( $isNotification ) );
        }


        /**
         * Get Relative time
         * @param string $text a date/time string
         * @param string $timezone [optional]
         * @param string $format [optional]
         * @param bool $isNotification [optional] set to true if call is notification
         * @return BaseJsonRpcCall (result: string)
         */
        public function date_GetRelativeTime( $text, $timezone = 'UTC', $format = 'c', $isNotification = false ) {
            return $this->call( __FUNCTION__, array( 'text' => $text, 'timezone' => $timezone, 'format' => $format ), $this->getRequestId( $isNotification ) );
        }


        /**
         * Implode Function
         * @param string $glue
         * @param array $pieces [optional]
         * @param bool $isNotification [optional] set to true if call is notification
         * @return BaseJsonRpcCall (result: string)
         */
        public function date_Implode( $glue, $pieces = array(  0 => '1',  1 => '2',  2 => '3'), $isNotification = false ) {
            return $this->call( __FUNCTION__, array( 'glue' => $glue, 'pieces' => $pieces ), $this->getRequestId( $isNotification ) );
        }


        /**
         * Get Ping Result
         * @param string $message [optional]
         * @param bool $isNotification [optional] set to true if call is notification
         * @return BaseJsonRpcCall (result: string)
         */
        public function ping_Ping( $message = 'pong', $isNotification = false ) {
            return $this->call( __FUNCTION__, array( 'message' => $message ), $this->getRequestId( $isNotification ) );
        }


        /**
         * Get Instance
         * @return DatePingServiceClient
         */
        public static function GetInstance() {
            return new self( 'http://eazyjsonrpc/tests/example-server.php' );
        }

    }