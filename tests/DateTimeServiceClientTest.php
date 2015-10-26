<?php


    /**
     * @group Client
     */
    class DateTimeServiceClientTest extends PHPUnit_Framework_TestCase {

        /**
         * @var DateTimeServiceClient
         */
        protected $object;

        protected $url = 'http://eazyjsonrpc/tests/example-server.php';


        public function setUp() {
            $this->object = new DateTimeServiceClient( $this->url );
        }


        public function testGetTime() {
            $response = $this->object->GetTime();
            static::assertEmpty( $response->Error );
            static::assertNotEmpty( $response->Result );
            $response = $this->object->GetTime( 'UTC', 'd.m.Y' );
            static::assertAttributeEquals( date( 'd.m.Y' ), 'Result', $response );
        }


        public function testGetTimeZones() {
            $response = $this->object->GetTimeZones();
            static::assertEquals( getCachedTimeZones(), $response->Result );
        }


        public function testGetRelativeTimeError() {
            $response = $this->object->GetRelativeTime( '-0000-00-00', '1' );
            static::assertEmpty( $response->Result );
            static::assertNotEmpty( $response->Error );
            static::assertTrue( $response->HasError() );

        }


        public function testBatchCalls() {
            static::assertTrue( $this->object->BeginBatch() );
            static::assertFalse( $this->object->BeginBatch() );

            $r1 = $this->object->GetRelativeTime( 'now' );
            $r2 = $this->object->GetRelativeTime( 'yesterday' );
            $r3 = $this->object->GetRelativeTime( 'yesterday', 'UTC', 'c', true );
            $r4 = $this->object->GetRelativeTime( 'yesterday' );

            static::assertEmpty( $r2->Result );

            static::assertTrue( $this->object->CommitBatch() );
            static::assertFalse( $this->object->CommitBatch() );

            static::assertNotEmpty( $r2->Result );
            static::assertEmpty( $r3->Result );
            static::assertEquals( $r4->Result, $r2->Result );
        }
    }