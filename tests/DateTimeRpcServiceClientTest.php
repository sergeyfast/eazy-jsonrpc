<?php
    include 'DateTimeServiceClientTest.php';


    /**
     * @group Client
     */
    class DateTimeRpcServiceClientTest extends DateTimeServiceClientTest {

        protected $url = 'http://eazyjsonrpc/tests/example-server.php?v2';


        public function setUp() {
            parent::setUp();
            $this->object->UseObjectsInResults = true;
        }


        public function testGetTimeZones() {
            $response = $this->object->GetTimeZones();
            static::assertCount( count(getCachedTimeZones()), $response->Result );
            static::assertInternalType( 'array', $response->Result );
        }
    }