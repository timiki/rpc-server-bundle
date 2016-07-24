<?php

namespace Timiki\Bundle\RpcServerBundle\Mapping;

/**
 * @Annotation
 * @Target("CLASS")
 */
final class Cache
{
	/**
	 * @var integer
	 */
	public $lifetime;

}
