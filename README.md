eazy-jsonrpc
============

PHP JSON-RPC 2.0 Server/Client Implementation with Automatic Client Class Generation via SMD

Server
------

SMD Schema available via /server.php?smd

__Public Namespace__

* Inherits your exposed class from BaseJsonRpcServer or create `new BaseJsonRpcServer( $instance );`
* `$server->Execute();`

__Multiple Namespaces__

* Create `new BaseJsonRpcServer();`
* Call `$server->RegisterInstance( $instance, $namespace )` as many times as you need
* `$server->Execute();`


Client
------

* Generate Client from SMD Schema from generator/ `php JsonRpcClientGenerator.php <smd-file> <class-name>`
* Use it:
```
$client = <class-name>::GetInstance(<url>);

try {  
$result = $client->Method(); 
} catch (BaseJsonRpcException $e) {
  // work with exception
}
```

Client with typed returns by rpcgen
------

* Generate Client from SMD Schema with [rpcgen](https://github.com/vmkteam/rpcgen) and save it to `RpcClient.php`
* Use it:
```
$client = RpcClient::GetInstance(<url>);

try {  
$result = $client->Method(); 
} catch (BaseJsonRpcException $e) {
  // work with exception
}
```

Doc
------
* cd generator
* `php SmdToSwaggerConverter.php 'http://eazyjsonrpc/tests/example-server.php?smd' eazyjsonrpc ../doc/swagger.json`
* open http://eazyjsonrpc/doc/