<?php
    /**
     * Simple Date Time Service
     * @author JsonRpcClientGenerator at 03.09.2012 18:32
     */
    class DateTimeServiceClient extends BaseJsonRpcClient {

        /**
         * Get Current Time
         * @param string $timezone       [optional]
         * @param string $format         [optional]
         * @param bool   $isNotification [optional] set to true if call is notification
         * @return BaseJsonRpcCall (result: string)
         */
        public function GetTime( $timezone = 'UTC', $format = 'c', $isNotification = false ) {
            return $this->call( __FUNCTION__, array( 'timezone' => $timezone, 'format' => $format ), $this->getRequestId( $isNotification ) );
        }


        /**
         * Returns associative array containing dst, offset and the timezone name
         * @param bool $isNotification [optional] set to true if call is notification
         * @return BaseJsonRpcCall (result: array)
         */
        public function GetTimeZones( $isNotification = false ) {
            return $this->call( __FUNCTION__, array(), $this->getRequestId( $isNotification ) );
        }


        /**
         * Get Relative time
         * @param string $text           a date/time string
         * @param string $timezone       [optional]
         * @param string $format         [optional]
         * @param bool   $isNotification [optional] set to true if call is notification
         * @return BaseJsonRpcCall (result: string)
         */
        public function GetRelativeTime( $text, $timezone = 'UTC', $format = 'c', $isNotification = false ) {
            return $this->call( __FUNCTION__, array( 'text' => $text, 'timezone' => $timezone, 'format' => $format ), $this->getRequestId( $isNotification ) );
        }


        /**
         * Implode Function
         * @param string $glue
         * @param array  $pieces         [optional]
         * @param bool   $isNotification [optional] set to true if call is notification
         * @return BaseJsonRpcCall (result: string)
         */
        public function Implode( $glue, $pieces = array( 0 => '1', 1 => '2', 2 => '3' ), $isNotification = false ) {
            return $this->call( __FUNCTION__, array( 'glue' => $glue, 'pieces' => $pieces ), $this->getRequestId( $isNotification ) );
        }


        /**
         * Get Instance
         */
        public function GetInstance() {
            return new self( 'http://eazyjsonrpc/example-server.php' );
        }

    }

?>