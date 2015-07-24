<?php


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
            $request  = array( 'jsonrpc' => '2.0', 'method' => 'GetTime', 'id' => self::RequestId );
            $response = $this->call( $request );
            $this->assertArrayHasKey( 'result', $response );
            $this->assertEquals( $response['id'], self::RequestId );

            $request  = array( 'jsonrpc' => '2.0', 'method' => 'GetTime', 'params' => array( 'format' => 'd.m.Y' ), 'id' => self::RequestId );
            $response = $this->call( $request );
            $this->assertArrayHasKey( 'result', $response );
            $this->assertEquals( date( 'd.m.Y' ), $response['result'] );
            $this->assertEquals( self::RequestId, $response['id'] );
        }


        public function testGetTimeWithoutNamedParams() {
            $request  = array( 'jsonrpc' => '2.0', 'method' => 'GetTime', 'params' => array( 'UTC', 'd.m.Y' ), 'id' => self::RequestId );
            $response = $this->call( $request );
            $this->assertArrayHasKey( 'result', $response );
            $this->assertEquals( date( 'd.m.Y' ), $response['result'] );
            $this->assertEquals( self::RequestId, $response['id'] );
        }


        public function testGetServiceMap() {
            $_GET['smd'] = true;
            $response    = $this->call( '' );
            $this->assertArrayHasKey( 'description', $response );
            $this->assertArrayHasKey( 'services', $response );
        }


        public function testGetTimeZones() {
            $request  = array( 'jsonrpc' => '2.0', 'method' => 'GetTimeZones', 'id' => self::RequestId );
            $response = $this->call( $request );
            $this->assertEquals( getCachedTimeZones(), $response['result'] );
        }


        public function testGetRelativeTimeError() {
            $request  = array( 'jsonrpc' => '2.0', 'method' => 'GetRelativeTime', 'id' => self::RequestId );
            $response = $this->call( $request );
            $this->assertArrayHasKey( 'error', $response );
            $this->assertEquals( BaseJsonRpcServer::InvalidParams, $response['error']['code'] );
        }


        public function testInvalidRequest() {
            $response = $this->call( '' );
            $this->assertArrayHasKey( 'error', $response );
            $this->assertEquals( BaseJsonRpcServer::InvalidRequest, $response['error']['code'] );

            $response = $this->call( null, false );
            $this->assertArrayHasKey( 'error', $response );
            $this->assertEquals( BaseJsonRpcServer::ParseError, $response['error']['code'] );
        }


        public function testBatchCalls() {
            $request1 = array( 'jsonrpc' => '2.0', 'method' => 'GetRelativeTime', 'params' => array( 'now' ), 'id' => self::RequestId );
            $request2 = array( 'jsonrpc' => '2.0', 'method' => 'GetRelativeTime', 'params' => array( 'yesterday' ), 'id' => self::RequestId + 1 );
            $request3 = array( 'jsonrpc' => '2.0', 'method' => 'GetRelativeTime', 'params' => array( 'yesterday' ) );
            $request4 = array( 'jsonrpc' => '2.0', 'method' => 'Implode', 'params' => array( ';' ), 'id' => self::RequestId + 2 );
            $response = $this->call( array( $request1, $request2, $request3, $request4 ) );
            $this->assertCount( 3, $response );

            foreach ( $response as $r ) {
                if ( $r['id'] == self::RequestId + 2 ) {
                    $this->assertEquals( '1;2;3', $r['result'] );
                }
            }
        }


        public function testMultipleBatchCalls() {
            $requests = array();
            for ( $i = 1; $i < 12; $i ++ ) {
                $requests[] = array( 'jsonrpc' => '2.0', 'method' => 'GetRelativeTime', 'params' => array( 'now' ), 'id' => $i );
            }

            $response = $this->call( $requests );
            $this->assertCount( 3, $response );
            $this->assertArrayHasKey( 'code', $response['error'] );
        }

    }