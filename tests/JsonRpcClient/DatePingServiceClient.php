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
     * Simple Date Time Service
     * Simple Echo Service
     * Simple News Service
     * 
     */
    class DatePingServiceClient extends BaseJsonRpcClient {

        /**
        * <date.GetTime> RPC method
        * Get Current Time
        * @param string|null $timezone [optional]
        * @param string|null $format [optional]
        * @param bool $isNotification [optional] set to true if call is notification
        * @return string
        * @throws BaseJsonRpcException
        * @throws GuzzleException
        * @throws JsonMapper_Exception
        */
        public function date_GetTime( string $timezone = 'UTC', string $format = 'c', $isNotification = false ): string {
            return $this->call( __FUNCTION__, 'string', [ 'timezone' => $timezone, 'format' => $format ], $this->getRequestId( $isNotification ) );
        }


        /**
        * <date.GetTimeZones> RPC method
        * Returns associative array containing dst, offset and the timezone name
        * @param bool $isNotification [optional] set to true if call is notification
        * @return array
        * @throws BaseJsonRpcException
        * @throws GuzzleException
        * @throws JsonMapper_Exception
        */
        public function date_GetTimeZones( $isNotification = false ): array {
            return $this->call( __FUNCTION__, 'array', [], $this->getRequestId( $isNotification ) );
        }


        /**
        * <date.GetRelativeTime> RPC method
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
        public function date_GetRelativeTime( string $text, string $timezone = 'UTC', string $format = 'c', $isNotification = false ): string {
            return $this->call( __FUNCTION__, 'string', [ 'text' => $text, 'timezone' => $timezone, 'format' => $format ], $this->getRequestId( $isNotification ) );
        }


        /**
        * <date.Implode> RPC method
        * Implode Function
        * @param string $glue
        * @param array|null $pieces [optional]
        * @param bool $isNotification [optional] set to true if call is notification
        * @return string string
        * @throws BaseJsonRpcException
        * @throws GuzzleException
        * @throws JsonMapper_Exception
        */
        public function date_Implode( string $glue, array $pieces = array(  0 => '1',  1 => '2',  2 => '3'), $isNotification = false ): string {
            return $this->call( __FUNCTION__, 'string', [ 'glue' => $glue, 'pieces' => $pieces ], $this->getRequestId( $isNotification ) );
        }


        /**
        * <ping.Ping> RPC method
        * Get Ping Result
        * @param string|null $message [optional]
        * @param bool $isNotification [optional] set to true if call is notification
        * @return string pong
        * @throws BaseJsonRpcException
        * @throws GuzzleException
        * @throws JsonMapper_Exception
        */
        public function ping_Ping( string $message = 'pong', $isNotification = false ): string {
            return $this->call( __FUNCTION__, 'string', [ 'message' => $message ], $this->getRequestId( $isNotification ) );
        }


        /**
        * <news.GetList> RPC method
        * Get News List
        * @param bool $isNotification [optional] set to true if call is notification
        * @return array News
        * @throws BaseJsonRpcException
        * @throws GuzzleException
        * @throws JsonMapper_Exception
        */
        public function news_GetList( $isNotification = false ): array {
            return $this->call( __FUNCTION__, 'array', [], $this->getRequestId( $isNotification ) );
        }


        /**
        * <news.GetById> RPC method
        * Get News By Id
        * @param int $id
        * @param bool $isNotification [optional] set to true if call is notification
        * @return array News
        * @throws BaseJsonRpcException
        * @throws GuzzleException
        * @throws JsonMapper_Exception
        */
        public function news_GetById( int $id, $isNotification = false ): array {
            return $this->call( __FUNCTION__, 'array', [ 'id' => $id ], $this->getRequestId( $isNotification ) );
        }


        /**
        * <news.GetTags> RPC method
        * Get Tags
        * @param bool $isNotification [optional] set to true if call is notification
        * @return array Category Tag
        * @throws BaseJsonRpcException
        * @throws GuzzleException
        * @throws JsonMapper_Exception
        */
        public function news_GetTags( $isNotification = false ): array {
            return $this->call( __FUNCTION__, 'array', [], $this->getRequestId( $isNotification ) );
        }


        /**
        * <news.Categories> RPC method
        * Get Categories
        * @param bool $isNotification [optional] set to true if call is notification
        * @return array
        * @throws BaseJsonRpcException
        * @throws GuzzleException
        * @throws JsonMapper_Exception
        */
        public function news_Categories( $isNotification = false ): array {
            return $this->call( __FUNCTION__, 'array', [], $this->getRequestId( $isNotification ) );
        }


        /**
        * <news.Search> RPC method
        * Search News
        * @param array $s NewsSearch
        * @param int $page
        * @param int|null $count [optional]
        * @param bool $isNotification [optional] set to true if call is notification
        * @return array News
        * @throws BaseJsonRpcException
        * @throws GuzzleException
        * @throws JsonMapper_Exception
        */
        public function news_Search( array $s, int $page, int $count = 50, $isNotification = false ): array {
            return $this->call( __FUNCTION__, 'array', [ 's' => $s, 'page' => $page, 'count' => $count ], $this->getRequestId( $isNotification ) );
        }


        /**
        * <news.NameValue> RPC method
        * Name Value
        * @param array $nv NameValue Parameter
        * @param int|null $c [optional]
        * @param bool $isNotification [optional] set to true if call is notification
        * @return array
        * @throws BaseJsonRpcException
        * @throws GuzzleException
        * @throws JsonMapper_Exception
        */
        public function news_NameValue( array $nv, int $c = null, $isNotification = false ): array {
            return $this->call( __FUNCTION__, 'array', [ 'nv' => $nv, 'c' => $c ], $this->getRequestId( $isNotification ) );
        }


        /**
         * Get Instance
         * @param $url string
         * @return DatePingServiceClient
         */
        public static function GetInstance( string $url ): DatePingServiceClient {
            return new self( $url );
        }

    }