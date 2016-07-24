JSON-RPC server bundle for symfony
==================================

JSON-RPC is a remote procedure call protocol encoded in JSON. It is a very simple protocol (and very similar to XML-RPC), defining only a handful of data types and commands. 
JSON-RPC allows for notifications (data sent to the server that does not require a response) and for multiple calls to be sent to the server which may be answered out of order.

[Wikipedia][1] | [Specification][2]

Install
-------

Add to composer from command line

    composer require timiki/rpc-client

or add in composer.json

    "require"     : {
        "timiki/rpc-server-bundle" : "^2.0"
    }

Configs
-------
    
    # RPC server
    rpc_server:
        path: ~
        cache: ~
        proxy:
            enable: false
            address: ~

Main configs:

**path** Path to JSON-RPC methods. Default null (Search dir Method in all bundles).

    // Methods in bundle
     
    path: 
        - "@AppBundle/Method"
        - "@MyBundle/Method"
     
    
    path: "@AppBundle/Method"
     
    // Or methods by puth
     
    path: 
        - "path/to/method"
        - "path/to/other/method"
        
    path: "path/to/method"
    
**cache** Cache service id. Service must be instance of Doctrine\Common\Cache\CacheProvider.

    cache: service.cache.id

Proxy configs:

**enable** Boolean Enable|disable Proxy not found method to another JSON-RPC server.

**address** Boolean Proxy not found method to another JSON-RPC server.

    address: "rpc.address"
    
    address: 
        - "rpc.address.one"
        - "rpc.address.two"

Controller
----------

You can use you own controller for JSON-RPC request. For example:

    <?php
    
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpFoundation\Request;
    
    class RpcController extends Controller
    {
        public function indexAction(Request $request)
        {
            return $this->get('rpc.server.handler')->handleHttpRequest($request);
        }
    }

or add default JSON-RPC route (default POST to /rpc) to you routing.yml

    rpc:
        resource: "@RpcServerBundle/Controller/"
        type:     annotation


If web site and JSON-RPC server located on a different domain remember about [CORS][3]. Use custom Controller for solve it.

Method
------

    <?php
    
    use Timiki\Bundle\RpcServerBundle\Mapping as Rpc;
    use Symfony\Component\Validator\Constraints as Assert;

    /**
     * @Rpc\Method("name")
     * @Rpc\Roles({
     *   "ROLE_NAME"
     * })
     * @Rpc\Cache(lifetime=3600)
     */
    class Method
    {
        /**
         * @Rpc\Param()
         * @Assert\NotBlank()
         */
        protected $param;
    
        /**
         * @Rpc\Execute()
         */
        public function execute()
        {
            $param = $this->param;
            
            ...
            
            return $result;
        }
    }
    
Annotation
----------

**@Method**

Define class as JSON-RPC method. 

    @Method("method name")

**@Roles**

Set roles for access to method. If user not granted for access server return error with message "Method not granted" and code "-32001".

     @Roles({
       "ROLE_NAME",
       "ROLE_OTHER",
     })

**@Cache**

If define cache in configs it set response lifetime.

    @Cache(lifetime=3600)

**@Param**

Define JSON-RPC params. Use Symfony\Component\Validator\Constraints for validate it.

        /**
         * @Param()
         */
        protected $param;
        
        /**
         * @Param()
         */
        protected $param = null'; // Default value for param

**@Execute**

Define execute function in class.

        /**
         * @Rpc\Execute()
         */
        public function someMethod()
        {
            // Code
        }

[1]: https://wikipedia.org/wiki/JSON-RPC
[2]: http://www.jsonrpc.org/specification
[3]: https://wikipedia.org/wiki/Cross-origin_resource_sharing