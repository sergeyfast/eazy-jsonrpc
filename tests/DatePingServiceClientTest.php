<?php


    /**
     * @group Client
     */
    class DatePingServiceClientTest extends PHPUnit_Framework_TestCase {

        /**
         * @var DatePingServiceClient
         */
        protected $object;

        protected $url = 'http://eazyjsonrpc/tests/example-server.php?v3';


        public function setUp() {
            $this->object = new DatePingServiceClient( $this->url );
        }


        public function testGetTime() {
            $response = $this->object->date_GetTime();
            $this->assertEmpty( $response->Error );
            $this->assertNotEmpty( $response->Result );
            $response = $this->object->date_GetTime( 'UTC', 'd.m.Y' );
            $this->assertAttributeEquals( date( 'd.m.Y' ), 'Result', $response );
        }


        public function testPing() {
            $response = $this->object->ping_Ping();
            $this->assertEquals( 'pong', $response->Result );
        }


        public function testGetTimeZones() {
            $response = $this->object->date_GetTimeZones();
            $this->assertEquals( getCachedTimeZones(), $response->Result );
        }


        public function testGetRelativeTimeError() {
            $response = $this->object->date_GetRelativeTime( '-0000-00-00', '1' );
            $this->assertEmpty( $response->Result );
            $this->assertNotEmpty( $response->Error );
            $this->assertTrue( $response->HasError() );

        }


        public function testBatchCalls() {
            $this->assertTrue( $this->object->BeginBatch() );
            $this->assertFalse( $this->object->BeginBatch() );

            $r1 = $this->object->date_GetRelativeTime( 'now' );
            $r2 = $this->object->date_GetRelativeTime( 'yesterday' );
            $r3 = $this->object->date_GetRelativeTime( 'yesterday', 'UTC', 'c', true );
            $r4 = $this->object->date_GetRelativeTime( 'yesterday' );
            $r5 = $this->object->ping_Ping( 'test' );

            $this->assertEmpty( $r2->Result );

            $this->assertTrue( $this->object->CommitBatch() );
            $this->assertFalse( $this->object->CommitBatch() );

            $this->assertNotEmpty( $r2->Result );
            $this->assertEmpty( $r3->Result );
            $this->assertEquals( $r4->Result, $r2->Result );
            $this->assertEquals( $r5->Result, 'test' );
        }
    }