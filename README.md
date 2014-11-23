eazy-jsonrpc
============

PHP JSON-RPC 2.0 Server/Client Implementation with Automatic Client Class Generation via SMD

Server
------

SMD Schema available via /server.php?smd

__Public Namespace__

* Inherits your exposed class from BaseJsonRpcServer or create `new BaseJsonRpcServer( $instance );`
* `$server->execute();`

__Multiple Namespaces__

* Create `new BaseJsonRpcServer();`
* Call `$server->RegisterInstance( $instance, $namespace )` as many times as you need
* `$server->execute();`


Client
------

* Generate Client from SMD Schema from generator/ `php JsonRpcClientGenerator <smd-file> <class-name>`
* Create client instance `$client = <class-name>::GetInstance();` or `$client = new <class-name>( <url> );`
* Use it `$result = $client->Method()`; :)

Doc
------
* cd doc
* `php SmdToSwaggerConverter.php 'http://eazyjsonrpc/tests/example-server.php?smd&v2' eazyjsonrpc ../doc/swagger.json`
* open http://eazyjsonrpc/doc/