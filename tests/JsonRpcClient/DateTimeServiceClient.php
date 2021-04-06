<?php
/**
* PHP RPC Client by JsonRpcClientGenerator
* @date 06.04.2021 13:23
*/

namespace JsonRpcClient;

	use EazyJsonRpc\BaseJsonRpcClient;
    use EazyJsonRpc\BaseJsonRpcException;
    use GuzzleHttp\Exception\GuzzleException;
    use JsonMapper_Exception;

    /**
     * Simple Date Time RPC Service
     * 
     */
    class DateTimeServiceClient extends BaseJsonRpcClient {

        /**
        * <GetTime> RPC method
        * Get Current Time
        * @param string|null $timezone [optional]
        * @param string|null $format [optional]
        * @param bool $isNotification [optional] set to true if call is notification
        * @return string
        * @throws BaseJsonRpcException
        * @throws GuzzleException
        * @throws JsonMapper_Exception
        */
        public function GetTime( string $timezone = 'UTC', string $format = 'c', $isNotification = false ): string {
            return $this->call( __FUNCTION__, 'string', [ 'timezone' => $timezone, 'format' => $format ], $this->getRequestId( $isNotification ) );
        }


        /**
        * <GetTimeZones> RPC method
        * Returns associative array containing dst, offset and the timezone name
        * @param bool $isNotification [optional] set to true if call is notification
        * @return array
        * @throws BaseJsonRpcException
        * @throws GuzzleException
        * @throws JsonMapper_Exception
        */
        public function GetTimeZones( $isNotification = false ): array {
            return $this->call( __FUNCTION__, 'array', [], $this->getRequestId( $isNotification ) );
        }


        /**
        * <GetRelativeTime> RPC method
        * Get Relative time
        * @param string $text a date/time string
        * @param string|null $timezone [optional]
        * @param string|null $format [optional]
        * @param bool $isNotification [optional] set to true if call is notification
        * @return string
        * @throws BaseJsonRpcException
        * @throws GuzzleException
        * @throws JsonMapper_Exception
        */
        public function GetRelativeTime( string $text, string $timezone = 'UTC', string $format = 'c', $isNotification = false ): string {
            return $this->call( __FUNCTION__, 'string', [ 'text' => $text, 'timezone' => $timezone, 'format' => $format ], $this->getRequestId( $isNotification ) );
        }


        /**
        * <Implode> RPC method
        * Implode Function
        * @param string $glue
        * @param array|null $pieces [optional]
        * @param bool $isNotification [optional] set to true if call is notification
        * @return string string
        * @throws BaseJsonRpcException
        * @throws GuzzleException
        * @throws JsonMapper_Exception
        */
        public function Implode( string $glue, array $pieces = array(  0 => '1',  1 => '2',  2 => '3'), $isNotification = false ): string {
            return $this->call( __FUNCTION__, 'string', [ 'glue' => $glue, 'pieces' => $pieces ], $this->getRequestId( $isNotification ) );
        }


        /**
        * <ComplexResult> RPC method
        * ComplexResult Function
        * @param bool $isNotification [optional] set to true if call is notification
        * @return array
        * @throws BaseJsonRpcException
        * @throws GuzzleException
        * @throws JsonMapper_Exception
        */
        public function ComplexResult( $isNotification = false ): array {
            return $this->call( __FUNCTION__, 'array', [], $this->getRequestId( $isNotification ) );
        }


        /**
         * Get Instance
         * @param $url string
         * @return DateTimeServiceClient
         */
        public static function GetInstance( string $url ): DateTimeServiceClient {
            return new self( $url );
        }

    }