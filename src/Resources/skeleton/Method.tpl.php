<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use Timiki\Bundle\RpcServerBundle\Attribute as RPC;

#[RPC\Method('<?php echo $method_name; ?>')]
class <?php echo $class_name; ?>
{
    #[RPC\Param]
    protected $param;

    public function __invoke(): mixed
    {
        // Method code...
    }
}