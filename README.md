# JSON-RPC

JSON-RPC is a remote procedure call protocol encoded in JSON. It is a very simple protocol (and very similar to XML-RPC), defining only a handful of data types and commands. 
JSON-RPC allows for notifications (data sent to the server that does not require a response) and for multiple calls to be sent to the server which may be answered out of order.

[Wikipedia][1] | [Specification][2]







## Configs

Add to you config.yml
    
    # RPC server
    rpc_server:
        paths:
           - {namespace: 'Methods namespace', path: 'Methods path'}
        methods:
           - {name: 'Methods name', path: 'Methods class path'}


## Method


    <?php
    
    use Timiki\Bundle\RpcServerBundle\Rpc\Method
    
    
    class MyMethod extend Method {
    
        /**
         * Get the method params
         *
         * @return array
         */
        public function getParams()
        {   
            return [
                ['id', 'integer|required'],
                ['name', 'string', '']
            ];
        }
        
        /**
         * Execute the server method
         */
        public function execute()
        {
            // Your code
        }
    
    }
    
    
    
    
    

#### type (string)

Type of RPC server

#### paths (array)

List dirs for search RPC server methods in next format:

{namespace: Namespace methods, path: Path to methods dir}

## Proxy configs

#### enable (boolean)

Enable|Disable use proxy to forward requests to remote RPC server

#### type (string)

Type of remote RPC server

#### address (array)

Address of remote RPC server

#### forwardHeaders (array)

List headers to forward to remote RPC server

#### forwardCookies (array)

List cookies to forward to remote RPC server

#### forwardCookiesDomain (string)

Set cookies domain name

#### forwardIp (boolean)

Enable|Disable forward ip to remote RPC server

#### forwardLocale (boolean)

Enable|Disable forward locale to remote RPC server


[1]: https://en.wikipedia.org/wiki/JSON-RPC
[2]: http://www.jsonrpc.org/specification