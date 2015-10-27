<?php


    /**
     * @group Client
     */
    class DatePingServiceClientTest extends PHPUnit_Framework_TestCase {

        /**
         * @var DatePingServiceClient
         */
        protected $object;

        protected $url = 'http://eazyjsonrpc/tests/example-server.php';


        public function setUp() {
            $this->object = new DatePingServiceClient( $this->url );
        }


        public function testGetTime() {
            $response = $this->object->date_GetTime();
            static::assertEmpty( $response->Error );
            static::assertNotEmpty( $response->Result );
            $response = $this->object->date_GetTime( 'UTC', 'd.m.Y' );
            static::assertAttributeEquals( date( 'd.m.Y' ), 'Result', $response );
        }


        public function testPing() {
            $response = $this->object->ping_Ping();
            static::assertEquals( 'pong', $response->Result );
        }


        public function testGetTimeZones() {
            $response = $this->object->date_GetTimeZones();
            static::assertEquals( getCachedTimeZones(), $response->Result );
        }


        public function testGetRelativeTimeError() {
            $response = $this->object->date_GetRelativeTime( '-0000-00-00', '1' );
            static::assertEmpty( $response->Result );
            static::assertNotEmpty( $response->Error );
            static::assertTrue( $response->HasError() );

        }


        public function testBatchCalls() {
            static::assertTrue( $this->object->BeginBatch() );
            static::assertFalse( $this->object->BeginBatch() );

            $r1 = $this->object->date_GetRelativeTime( 'now' );
            $r2 = $this->object->date_GetRelativeTime( 'yesterday' );
            $r3 = $this->object->date_GetRelativeTime( 'yesterday', 'UTC', 'c', true );
            $r4 = $this->object->date_GetRelativeTime( 'yesterday' );
            $r5 = $this->object->ping_Ping( 'test' );

            static::assertEmpty( $r2->Result );

            static::assertTrue( $this->object->CommitBatch() );
            static::assertFalse( $this->object->CommitBatch() );

            static::assertNotEmpty( $r2->Result );
            static::assertEmpty( $r3->Result );
            static::assertEquals( $r4->Result, $r2->Result );
            static::assertEquals( $r5->Result, 'test' );
        }
    }