<?php
    use EazyJsonRpc\BaseJsonRpcServer;


    /**
     * @group Server
     */
    class DateTimeRpcServiceTest extends PHPUnit_Framework_TestCase {

        /**
         * @var BaseJsonRpcServer
         */
        protected $object;

        const RequestId = 1;


        public function setUp() {
            $this->object = new DateTimeRpcService();
        }


        private function call( $result, $encode = true ) {
            $_GET['rawRequest'] = $encode ? json_encode( $result ) : $result;
            ob_start();
            $this->object->Execute();
            $data = ob_get_contents();
            ob_end_clean();

            return json_decode( $data, true );
        }


        public function testGetTime() {
            $request  = [ 'jsonrpc' => '2.0', 'method' => 'GetTime', 'id' => self::RequestId ];
            $response = $this->call( $request );
            static::assertArrayHasKey( 'result', $response );
            static::assertEquals( $response['id'], self::RequestId );

            $request  = [ 'jsonrpc' => '2.0', 'method' => 'GetTime', 'params' => [ 'format' => 'd.m.Y' ], 'id' => self::RequestId ];
            $response = $this->call( $request );
            static::assertArrayHasKey( 'result', $response );
            static::assertEquals( date( 'd.m.Y' ), $response['result'] );
            static::assertEquals( self::RequestId, $response['id'] );
        }


        public function testGetTimeWithoutNamedParams() {
            $request  = [ 'jsonrpc' => '2.0', 'method' => 'GetTime', 'params' => [ 'UTC', 'd.m.Y' ], 'id' => self::RequestId ];
            $response = $this->call( $request );
            static::assertArrayHasKey( 'result', $response );
            static::assertEquals( date( 'd.m.Y' ), $response['result'] );
            static::assertEquals( self::RequestId, $response['id'] );
        }


        public function testGetServiceMap() {
            $_GET['smd'] = true;
            $response    = $this->call( '' );
            static::assertArrayHasKey( 'description', $response );
            static::assertArrayHasKey( 'services', $response );
        }


        public function testGetTimeZones() {
            $request  = [ 'jsonrpc' => '2.0', 'method' => 'GetTimeZones', 'id' => self::RequestId ];
            $response = $this->call( $request );
            static::assertEquals( getCachedTimeZones(), $response['result'] );
        }


        public function testGetRelativeTimeError() {
            $request  = [ 'jsonrpc' => '2.0', 'method' => 'GetRelativeTime', 'id' => self::RequestId ];
            $response = $this->call( $request );
            static::assertArrayHasKey( 'error', $response );
            static::assertEquals( BaseJsonRpcServer::InvalidParams, $response['error']['code'] );
        }


        public function testInvalidRequest() {
            $response = $this->call( '' );
            static::assertArrayHasKey( 'error', $response );
            static::assertEquals( BaseJsonRpcServer::InvalidRequest, $response['error']['code'] );

            $response = $this->call( null, false );
            static::assertArrayHasKey( 'error', $response );
            static::assertEquals( BaseJsonRpcServer::ParseError, $response['error']['code'] );
        }


        public function testBatchCalls() {
            $request1 = [ 'jsonrpc' => '2.0', 'method' => 'GetRelativeTime', 'params' => [ 'now' ], 'id' => self::RequestId ];
            $request2 = [ 'jsonrpc' => '2.0', 'method' => 'GetRelativeTime', 'params' => [ 'yesterday' ], 'id' => self::RequestId + 1 ];
            $request3 = [ 'jsonrpc' => '2.0', 'method' => 'GetRelativeTime', 'params' => [ 'yesterday' ] ];
            $request4 = [ 'jsonrpc' => '2.0', 'method' => 'Implode', 'params' => [ ';' ], 'id' => self::RequestId + 2 ];
            $response = $this->call( [ $request1, $request2, $request3, $request4 ] );
            static::assertCount( 3, $response );

            foreach ( $response as $r ) {
                if ( $r['id'] === self::RequestId + 2 ) {
                    static::assertEquals( '1;2;3', $r['result'] );
                }
            }
        }


        public function testMultipleBatchCalls() {
            $requests = [ ];
            for ( $i = 1; $i < 12; $i++ ) {
                $requests[] = [ 'jsonrpc' => '2.0', 'method' => 'GetRelativeTime', 'params' => [ 'now' ], 'id' => $i ];
            }

            $response = $this->call( $requests );
            static::assertCount( 3, $response );
            static::assertArrayHasKey( 'code', $response['error'] );
        }

    }