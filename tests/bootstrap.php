<?php
    include '../src/BaseJsonRpcServer.php';
    include '../src/BaseJsonRpcClient.php';
    include 'lib/DateTimeService.php';
    include 'lib/DateTimeServiceClient.php';
    include 'lib/DatePingServiceClient.php';
    include 'lib/DateTimeRpcService.php';

    /**
     * @return mixed
     */
    function getCachedTimeZones() {
        static $result;
        if ( !$result ) {
            $result = json_decode( json_encode( DateTimeZone::listAbbreviations() ), true );
        }

        return $result;
    }
