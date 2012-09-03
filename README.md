eazy-jsonrpc
============

PHP JSON-RPC 2.0 Server/Client Implementation with Automatic Client Class Generation via SMD

Server
------

* Inherits your exposed class from BaseJsonRpcServer or create `new BaseJsonRpcServer( $instance );`
* `$server->execute();`

SMD Schema available via /server.php?smd

Client
------

* Generate Client from SMD Schema from generator/ `php JsonRpcClientGenerator <smd-file> <class-name>`
* Create client instance `$client = <class-name>::GetInstance();` or `$client = new <class-name>( <url> );`
* Use it `$result = $client->Method()`; :)
