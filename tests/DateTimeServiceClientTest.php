<?php

    use EazyJsonRpc\BaseJsonRpcException;
    use JsonRpcClient\DateTimeServiceClient;


    /**
     * @group Client
     */
    class DateTimeServiceClientTest extends PHPUnit_Framework_TestCase {

        /**
         * @var DateTimeServiceClient
         */
        protected $object;

        protected $url = 'http://localhost:8000/tests/example-server.php?v3';


        public function setUp() {
            $this->object = DateTimeServiceClient::GetInstance( $this->url );
        }


        public function testGetTime() {
            $response = $this->object->GetTime();
            static::assertNotEmpty( $response );
            $response = $this->object->GetTime( 'UTC', 'd.m.Y' );
            static::assertEquals( date( 'd.m.Y' ), $response );
        }


        public function testGetTimeZones() {
            $response = $this->object->GetTimeZones();
            static::assertEquals( getCachedTimeZones(), $response );
        }


        public function testGetRelativeTimeError() {
            try {
                $response = $this->object->GetRelativeTime( '-0000-00-00', '1' );
                static::assertEmpty( $response );
            } catch ( BaseJsonRpcException $e ) {
                static::assertNotEmpty( $e );
            }
        }


        public function testBatchCalls() {
            static::assertTrue( $this->object->BeginBatch() );
            static::assertFalse( $this->object->BeginBatch() );

            $this->object->GetRelativeTime( 'now' );
            $this->object->GetRelativeTime( 'exception' );
            $this->object->GetRelativeTime( 'yesterday' );
            $this->object->GetRelativeTime( 'yesterday' );

            list ( $r1, $r2, $r3, $r4 ) = $this->object->CommitBatch();
            static::assertNotEmpty( $r1 );
            static::assertEmpty( $this->object->CommitBatch() );
            static::assertInstanceOf( BaseJsonRpcException::class, $r2 );
            static::assertEquals( $r3, $r4 );
        }
    }