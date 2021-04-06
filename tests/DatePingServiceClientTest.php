<?php

    use JsonRpcClient\DatePingServiceClient;


    /**
     * @group Client
     */
    class DatePingServiceClientTest extends PHPUnit_Framework_TestCase {

        /**
         * @var DatePingServiceClient
         */
        protected $object;

        protected $url = 'http://localhost:8000/tests/example-server.php';


        public function setUp() {
            $this->object = DatePingServiceClient::GetInstance( $this->url );
        }


        public function testGetTime() {
            $response = $this->object->date_GetTime();
            static::assertNotEmpty( $response );
            $response = $this->object->date_GetTime( 'UTC', 'd.m.Y' );
            static::assertEquals( date( 'd.m.Y' ), $response );
        }


        public function testPing() {
            $response = $this->object->ping_Ping();
            static::assertEquals( 'pong', $response );
        }


        public function testGetTimeZones() {
            $response = $this->object->date_GetTimeZones();
            static::assertEquals( getCachedTimeZones(), $response );
        }


        public function testGetRelativeTimeError() {
            try {
                $response = $this->object->date_GetRelativeTime( '-0000-00-00', '1' );
                static::assertEmpty( $response );
            } catch ( \EazyJsonRpc\BaseJsonRpcException $e ) {
                static::assertNotEmpty( $e );
            }

        }


        public function testBatchCalls() {
            static::assertTrue( $this->object->BeginBatch() );
            static::assertFalse( $this->object->BeginBatch() );

            $this->object->date_GetRelativeTime( 'now' );
            $this->object->date_GetRelativeTime( 'yesterday' );
            $this->object->date_GetRelativeTime( 'yesterday', 'UTC', 'c', true );
            $this->object->date_GetRelativeTime( 'yesterday' );
            $this->object->ping_Ping( 'test' );

            $res = $this->object->CommitBatch();
            static::assertEmpty( $this->object->CommitBatch() );
            static::assertCount(4, $res);

            list($r1, $r2, $r3, $r4) = $res;
            static::assertNotEmpty( $r1 );
            static::assertEquals( $r2, $r3 );
            static::assertEquals( $r4, 'test' );
        }
    }