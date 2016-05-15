# JSON-RPC

JSON-RPC is a remote procedure call protocol encoded in JSON. It is a very simple protocol (and very similar to XML-RPC), defining only a handful of data types and commands. 
JSON-RPC allows for notifications (data sent to the server that does not require a response) and for multiple calls to be sent to the server which may be answered out of order.

[Wikipedia][1] | [Specification][2]


## Install


## Configs

Add to you config.yml
    
    # RPC server
    rpc_server:
        methods:
           - {name: 'Methods name', class: 'Methods class'}
        namespace:
           - Namespace

## Controller

You can use you own controller for RPC request. For example:

    <?php
    
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpFoundation\Request;
    
    class RpcController extends Controller
    {
        public function indexAction(Request $request)
        {
            return $this->get('rpc.server')->handleHttpRequest($request);
        }
    }

or add default rpc route (/rpc) to you routing.yml

    rpc:
        resource: "@RpcServerBundle/Controller/"
        type:     annotation
        

## Method

### Create

All RPC method class mast be extend from Timiki\Bundle\RpcServerBundle\Rpc\Method.

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
    
## Proxy

You can proxy request to another RPC server if method not found.

### Configs

Add to you config rpc_server
    
    # RPC server
    rpc_server:
        proxy:
            enable: boolean
            address: array|string 
            forwardHeaders: array
            forwardCookies: array
            forwardCookiesDomain: string
            headers: array
            cookies: array

**enable** (boolean)

Enable|Disable use proxy to forward requests to remote RPC server

**address** (array)

Address of remote RPC server

**forwardHeaders** (array)

List headers to forward to remote RPC server

**forwardCookies** (array)

List cookies to forward to remote RPC server

**forwardCookiesDomain** (string)

Set cookies domain name

**headers**

**cookies**


[1]: https://en.wikipedia.org/wiki/JSON-RPC
[2]: http://www.jsonrpc.org/specification