<?php
    include 'BaseJsonRpcServer.php';
    include 'tests/lib/DateTimeService.php';
    include 'tests/lib/DateTimeRpcService.php';

    /** @var BaseJsonRpcServer $server */
    $server = null;

    // inheritance mode
    if ( array_key_exists( 'v2', $_GET ) ) {
        $server = new DateTimeRpcService();
    } else {
        // Instance Mode
        $server = new BaseJsonRpcServer( new DateTimeService() );
    }

    $server->ContentType = null;
    $server->Execute();
?>