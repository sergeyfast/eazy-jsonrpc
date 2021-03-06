<?php
    include 'DateTimeRpcServiceTest.php';

    use EazyJsonRpc\BaseJsonRpcServer;


    /**
     * @group Server
     */
    class DateTimeServiceTest extends DateTimeRpcServiceTest {

        /**
         * @var BaseJsonRpcServer
         */
        protected $object;

        const RequestId = 1;


        public function setUp() {
            $this->object = new BaseJsonRpcServer( new DateTimeService() );
        }
    }