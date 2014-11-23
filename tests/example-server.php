<?php
    include '../src/BaseJsonRpcServer.php';
    include 'lib/DateTimeService.php';
    include 'lib/PingService.php';
    include 'lib/DateTimeRpcService.php';

    /** @var BaseJsonRpcServer $server */
    $server = null;

    // inheritance mode
    if ( array_key_exists( 'v2', $_GET ) ) {
        $server = new DateTimeRpcService();
    } else if ( array_key_exists( 'v3', $_GET ) ) {
        $server = new BaseJsonRpcServer();
        $server->RegisterInstance( new DateTimeService(), 'date' )
            ->RegisterInstance( new PingService(), 'ping' );
    } else {
        // Instance Mode
        $server = new BaseJsonRpcServer( new DateTimeService() );
    }

    $server->ContentType = null;
    $server->Execute();