<?php
    use EazyJsonRpc\BaseJsonRpcServer;

    include '../src/BaseJsonRpcServer.php';
    include '../src/BaseJsonRpcServerSmd.php';
    include 'lib/DateTimeService.php';
    include 'lib/PingService.php';
    include 'lib/DateTimeRpcService.php';
    include 'lib/NewsService.php';
    include 'lib/Model.php';

    /** @var BaseJsonRpcServer $server */
    $server = null;

    // inheritance mode
    if ( array_key_exists( 'v2', $_GET ) ) {
        $server = new DateTimeRpcService();
    } else if ( array_key_exists( 'v3', $_GET ) ) {
        // Instance Mode
        $server = new BaseJsonRpcServer( new DateTimeService() );
    } else {
        $server = new BaseJsonRpcServer();
        $server->RegisterInstance( new DateTimeService(), 'date' )
            ->RegisterInstance( new PingService(), 'ping' )
            ->RegisterInstance( new NewsService(), 'news' );
    }

    $server->Execute();