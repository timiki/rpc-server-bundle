<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\EventSubscriber;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Timiki\Bundle\RpcServerBundle\Event\JsonExecuteEvent;
use Timiki\Bundle\RpcServerBundle\Event\JsonRequestEvent;
use Timiki\RpcCommon\JsonResponse;

class CacheSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly CacheItemPoolInterface|null $cacheItemPool = null
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            JsonRequestEvent::class => ['onRequest', -4096],
            JsonExecuteEvent::class => ['onExecute', -4096],
        ];
    }

    public function onRequest(JsonRequestEvent $event): void
    {
        if (null === $this->cacheItemPool) {
            return;
        }

        $request = $event->getJsonRequest();
        $mapper = $event->getMapper();
        $meta = $mapper->getMetaData($request->getMethod());

        if (empty($meta->get('cache'))) {
            return;
        }

        $key = "{$mapper->getHash()}.{$request->getHash()}";
        $cache = $this->cacheItemPool->getItem($key);

        if ($cache->isHit()) {
            $response = new JsonResponse($request);
            $response->setResult($cache->get());

            $event->setJsonResponse($response);
        }
    }

    public function onExecute(JsonExecuteEvent $event): void
    {
        if (null === $this->cacheItemPool) {
            return;
        }

        $request = $event->getJsonRequest();
        $mapper = $event->getMapper();
        $meta = $event->getMetadata();

        if (empty($meta->get('cache'))) {
            return;
        }

        $key = "{$mapper->getHash()}.{$request->getHash()}";
        $item = $this->cacheItemPool->getItem($key);
        $item->expiresAfter((int) $meta->get('cache'));
        $item->set($event->getResult());

        $this->cacheItemPool->save($item);
    }
}
