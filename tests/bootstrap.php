<?php
    include '../src/EazyJsonRpc/BaseJsonRpcServer.php';
    include '../src/EazyJsonRpc/BaseJsonRpcServerSmd.php';
    include '../src/EazyJsonRpc/BaseJsonRpcClient.php';
    include 'JsonRpcClient/DateTimeServiceClient.php';
    include 'JsonRpcClient/DatePingServiceClient.php';
    include 'lib/DateTimeService.php';
    include 'lib/DateTimeRpcService.php';

    /**
     * @return mixed
     */
    function getCachedTimeZones() {
        static $result;
        if ( !$result ) {
            $result = json_decode( json_encode( DateTimeZone::listIdentifiers( DateTimeZone::AMERICA ) ), true );
        }

        return $result;
    }
