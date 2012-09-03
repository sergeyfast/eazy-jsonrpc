<?php
    /**
     * @group Client
     */
    class DateTimeServiceClientTest extends PHPUnit_Framework_TestCase {

        /**
         * @var DateTimeServiceClient
         */
        protected $object;

        protected $url = 'http://eazyjsonrpc/example-server.php';


        public function setUp() {
            $this->object = new DateTimeServiceClient( $this->url );
        }


        public function testGetTime() {
            $response = $this->object->GetTime();
            $this->assertEmpty( $response->Error );
            $this->assertNotEmpty( $response->Result );

            $response = $this->object->GetTime( 'UTC', 'd.m.Y' );
            $this->assertAttributeEquals( date( 'd.m.Y' ), 'Result', $response );
        }


        public function testGetTimeZones() {
            $response = $this->object->GetTimeZones();
            $this->assertEquals( getCachedTimeZones(), $response->Result );
        }


        public function testGetRelativeTimeError() {
            $response = $this->object->GetRelativeTime( '', '-1' );
            $this->assertEmpty( $response->Result );
            $this->assertNotEmpty( $response->Error );
            $this->assertTrue( $response->HasError() );

        }


        public function testBatchCalls() {
            $this->assertTrue( $this->object->BeginBatch() );
            $this->assertFalse( $this->object->BeginBatch() );

            $r1 = $this->object->GetRelativeTime( 'now' );
            $r2 = $this->object->GetRelativeTime( 'yesterday' );
            $r3 = $this->object->GetRelativeTime( 'yesterday', 'UTC', 'c', true );
            $r4 = $this->object->GetRelativeTime( 'yesterday' );

            $this->assertEmpty( $r2->Result );

            $this->assertTrue( $this->object->CommitBatch() );
            $this->assertFalse( $this->object->CommitBatch() );

            $this->assertNotEmpty( $r2->Result );
            $this->assertEmpty( $r3->Result );
            $this->assertEquals( $r4->Result, $r2->Result );
        }
    }

?>
