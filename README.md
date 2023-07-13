JSON-RPC Server bundle for symfony
==================================

[![Build Status](https://travis-ci.com/timiki/rpc-server-bundle.svg?branch=master)](https://travis-ci.com/timiki/rpc-server-bundle)

JSON-RPC is a remote procedure call protocol encoded in JSON. It is a very simple protocol (and very similar to XML-RPC)
, defining only a handful of data types and commands. JSON-RPC allows for notifications (data sent to the server that
does not require a response) and for multiple calls to be sent to the server which may be answered out of order.

[Wikipedia][1] | [Specification][2]

Install
-------

Symfony >= 6.0

```bash
composer require timiki/rpc-server-bundle "^6.0"
```

Symfony >= 5.0 use version ^5.0

```bash
composer require timiki/rpc-server-bundle "^5.0"
```

Symfony >= 4.3 use version ^4.1

```bash
composer require timiki/rpc-server-bundle "^4.1"
```

Symfony < 4.3 use version ^4.0

```bash
composer require timiki/rpc-server-bundle "^4.0"
```

Configs
-------

```yaml

rpc_server:
    
    # Mapping configs

    # Default mapping
    #   mapping: '%kernel.project_dir%/src/Method' 
    #
    # Multi dir mapping
    #   mapping:
    #   - '%kernel.project_dir%/src/Method1'
    #   - '%kernel.project_dir%/src/Method2'  
    # 
    # Multi handler|dir mapping
    #   mapping:
    #    v1:
    #       - '%kernel.project_dir%/src/Method/V1'
    #    v2:
    #       - '%kernel.project_dir%/src/Method/V2'
    #   
    
    mapping: '%kernel.project_dir%/src/Method'
            
    # Cache pool name
    
    cache: null
    
    # Serializer service, must be instanced of Timiki\Bundle\RpcServerBundle\Serializer\SerializerInterface
    # By default use Timiki\Bundle\RpcServerBundle\Serializer\BaseSerializer
    
    serializer: null

    parameters:
        
        # Allow extra params in JSON request
        allow_extra_params: false

``` 

Add methods dir to exclude from autowire

```yaml
    App\:
        resource: '../src/*'
        exclude: '../src/{Method}'
```

Controller
----------

Default:

You can use you own controller for JSON-RPC request. For example:

```php
<?php

declare(strict_types=1);

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class RpcController extends AbstractController
{
    public function indexAction(Request $request)
    {
        return $this->get('rpc.server.http_handler.default')->handleHttpRequest($request);
    }
}
```

or add default JSON-RPC route (default POST to /rpc) to you routing.yml

```yaml    
rpc:
    path: /rpc
    defaults: { _controller: Timiki\Bundle\RpcServerBundle\Controller\RpcController::handlerAction }
    methods: [POST]
```

or controller for different handler (version)

```yaml    
rpc:
    path: /{version}
    defaults: { _controller: Timiki\Bundle\RpcServerBundle\Controller\RpcController::handlerAction, version: v1 }
    methods: [POST]
```

If web site and JSON-RPC server located on a different domain remember about [CORS][3].


Method
------

```php
<?php

declare(strict_types=1);

use Timiki\Bundle\RpcServerBundle\Attribute as RPC;
use Symfony\Component\Validator\Constraints as Assert;

#[RPC\Method("name")]
#[RPC\Roles(["ROLE_NAME"])]
#[RPC\Cache(3600)]
class Method
{
    #[RPC\Param]
    #[Assert\NotBlank]
    protected $param;

    #[RPC\Execute] 
    public function execute()
    {
        $param = $this->param;
        
        ...
        
        return $result;
    }
}
    
```

Or you can also use __invoke to declare a call method

```php
<?php

declare(strict_types=1);

use Timiki\Bundle\RpcServerBundle\Attribute as RPC;
use Symfony\Component\Validator\Constraints as Assert;

#[RPC\Method("name")]
#[RPC\Roles(["ROLE_NAME"])]
#[RPC\Cache(3600)]
class Method
{
    #[RPC\Param]
    #[Assert\NotBlank]
    protected $param;

    public function __invoke()
    {
        $param = $this->param;
        
        ...
        
        return $result;
    }
}
    
```

Inject method execute Context

```php
<?php

declare(strict_types=1);

use Timiki\Bundle\RpcServerBundle\Attribute as RPC;
use Timiki\Bundle\RpcServerBundle\Method\Context;
use Symfony\Component\Validator\Constraints as Assert;

#[RPC\Method("name")]
#[RPC\Roles(["ROLE_NAME"])]
#[RPC\Cache(3600)]
class Method
{
    #[RPC\Param]
    #[Assert\NotBlank]
    protected $param;

    public function __invoke(Context $context)
    {
        $param = $this->param;
        
        ...
        
        return $result;
    }
}
    
```

Attributes
----------

**Method**

Define class as JSON-RPC method.

```php
#[Method("name")]
```

**Roles**

Set roles for access to method. If user not granted for access server return error with message "Method not granted" and
code "-32001".

```php
#[Roles(["ROLE_NAME", "ROLE_OTHER"])]
```

**Cache**

If define cache in configs it set response lifetime.

```php
#[Cache(3600)]
```

**Param**

Define JSON-RPC params. Use Symfony\Component\Validator\Constraints for validate it.

```php
#[Param]
protected $param;

#[Param]
protected $param = null'; // Default value for param
```

**Execute**

Define execute function in class.

```php
#[Execute]
public function execute()
{
    // Code
}
```

or use __invoke

```php
public function __invoke()
{
    // Code
}
```

Serialize
----------

For convert output result from method to json in bundle used serializer

Config for serializer:

```yaml    
rpc:
    serializer: rpc.server.serializer.base # serialize service id
```

In bundle include next serializers:

**rpc.server.serializer.base** - (default) use Symfony serialize to convert result to json
**rpc.server.serializer.role** - use user roles
as @Group (@see https://symfony.com/doc/current/components/serializer.html) for control access to output array

Create custom serializer

Here is an example of a simple class for serialization.

```php

<?php

declare(strict_types=1);

namespace App\Serializer;

use Timiki\Bundle\RpcServerBundle\Serializer\SerializerInterface;

class MySerializer implements SerializerInterface
{
    public function serialize(mixed $data): string 
        // You serialize logic
    }
    
    public function toArray(mixed $data): array 
        // You serialize logic
    }
}
```

And then add custom serializer service id to config

```yaml    
rpc:
    serializer: MySerializer # serialize service id
```

[1]: https://wikipedia.org/wiki/JSON-RPC

[2]: http://www.jsonrpc.org/specification

[3]: https://wikipedia.org/wiki/Cross-origin_resource_sharing