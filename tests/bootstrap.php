<?php
    include '../BaseJsonRpcServer.php';
    include '../BaseJsonRpcClient.php';
    include 'lib/DateTimeService.php';
    include 'lib/DateTimeServiceClient.php';
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
?>