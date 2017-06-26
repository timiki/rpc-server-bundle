<?php

namespace Timiki\Bundle\RpcServerBundle\Mapper;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\HttpKernel\KernelInterface;
use Timiki\Bundle\RpcServerBundle\Exceptions\InvalidMappingException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\DocParser;
use Timiki\Bundle\RpcServerBundle\Traits\CacheTrait;
use Timiki\Bundle\RpcServerBundle\Traits\StopwatchTrait;

/**
 * RPC Mapper.
 *
 * Service for found RPC methods in bundles and mapping methods metadata.
 */
class Mapper
{
    use CacheTrait;
    use StopwatchTrait;

    /**
     * @var AnnotationReader
     */
    protected $reader;

    /**
     * @var array Paths for mapping
     */
    protected $paths = [];

    /**
     * @var array Files for mapping
     */
    protected $files = [];

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @var array
     */
    protected static $loadFiles = [];

    /**
     * Mapper constructor.
     */
    public function __construct()
    {
        $this->reader = new AnnotationReader(new DocParser());

        AnnotationRegistry::registerFile(__DIR__.'/../Mapping/Param.php');
        AnnotationRegistry::registerFile(__DIR__.'/../Mapping/Execute.php');
        AnnotationRegistry::registerFile(__DIR__.'/../Mapping/Cache.php');
        AnnotationRegistry::registerFile(__DIR__.'/../Mapping/Method.php');
        AnnotationRegistry::registerFile(__DIR__.'/../Mapping/Roles.php');
    }

    /**
     * Set Kernel.
     *
     * @param KernelInterface $kernel
     */
    public function setKernel(KernelInterface $kernel = null)
    {
        $this->kernel = $kernel;
    }

    /**
     * Get annotation reader.
     *
     * @return AnnotationReader
     */
    public function getReader()
    {
        return $this->reader;
    }

    /**
     * Add path for mapping rpc methods.
     *
     * @param string $path
     *
     * @return void
     */
    public function addPath($path)
    {
        if ($path[0] === '@' && $this->kernel) {
            try {
                $path = $this->kernel->locateResource($path);
            } catch (\Exception $e) {
                return;
            }
        }

        if (is_dir($path)) {
            $this->paths[] = $path;
        }
    }

    /**
     * Add file for mapping rpc methods.
     *
     * @param string $file
     * @return $this
     * @throws \Timiki\Bundle\RpcServerBundle\Exceptions\InvalidMappingException
     */
    public function addFile($file)
    {
        if (is_file($file)) {
            $this->files[] = $file;
        } else {
            throw new InvalidMappingException(
                sprintf(
                    'File does not exist for mapping RPC Method "%s"',
                    $file
                )
            );
        }

        return $this;
    }

    /**
     * Load all metadata from mapping path.
     *
     * @return array
     * @throws InvalidMappingException
     */
    public function loadMetadata()
    {
        if (!empty($this->meta)) {
            return $this->meta;
        }

        $cacheId = 'rpc.meta.'.md5(implode(',', $this->paths));

        if ($this->cache && !$this->kernel->isDebug() && $this->cache->fetch($cacheId)) {
            return $this->meta = $this->cache->fetch($cacheId);
        }

        if ($this->stopwatch) {
            $this->stopwatch->start('rpc.mapping');
        }

        $meta = [
            'methods' => [],
            'classes' => [],
        ];

        $processMeta = function ($methodMeta) use (&$meta) {
            $meta['methods'][$methodMeta['method']->value] = $methodMeta;
            $meta['classes'][$methodMeta['class']]         = $methodMeta;
        };

        // Process files
        foreach ($this->files as $file) {
            if ($methodMeta = $this->loadFileMetadata($file)) {
                $processMeta($methodMeta);
            }
        }

        // Process dirs
        foreach ($this->paths as $path) {
            foreach ($this->loadPathMetadata($path) as $methodMeta) {
                $processMeta($methodMeta);
            }
        }

        if ($this->cache) {
            $this->cache->save($cacheId, $meta);
        }

        $this->meta = $meta;

        if ($this->stopwatch) {
            $this->stopwatch->stop('rpc.mapping');
        }

        return $meta;
    }

    /**
     * Load mapping metadata for all find PRC methods in path.
     *
     * @param string $path Mapping path
     * @return array
     * @throws InvalidMappingException
     */
    private function loadPathMetadata($path)
    {
        $meta = [];

        if (is_dir($path)) {

            $dir = new \DirectoryIterator($path);

            foreach ($dir as $file) {


                if ($file->isFile()) {

                    if ($methodsMeta = $this->loadFileMetadata($file->getRealPath())) {
                        foreach ($methodsMeta as $methodMeta) {
                            $meta[] = $methodMeta;
                        }
                    }

                }

                if ($file->isDir() && !$file->isDot()) {
                    array_push($meta, ...$this->loadPathMetadata($file->getRealPath()));
                }

            }

        }

        return $meta;
    }

    /**
     * load RPC method metadata for object.
     *
     * @param object $object
     * @return array|null
     * @throws InvalidMappingException
     */
    public function loadObjectMetadata($object)
    {
        $reflection = new \ReflectionObject($object);

        return $this->loadClassMetadata($reflection->getName());
    }

    /**
     * Load RPC method metadata from class.
     *
     * @param string $class Class
     * @return array|null
     * @throws InvalidMappingException
     */
    public function loadClassMetadata($class)
    {
        if (array_key_exists($class, $this->meta)) {
            return $this->meta[$class];
        }

        $meta = null;

        if (class_exists($class)) {

            $reflectionClass = new \ReflectionClass($class);

            if ($method = $this->reader->getClassAnnotation($reflectionClass, 'Timiki\Bundle\RpcServerBundle\Mapping\Method')) {

                $meta = [];

                if (empty($method->value)) {
                    throw new InvalidMappingException(
                        sprintf(
                            '@Method annotation must have name in class "%s", @Method("method name")',
                            $class
                        )
                    );
                }

                $meta['method'] = $method;
                $meta['class']  = $class;
                $meta['file']   = $reflectionClass->getFileName();

                // Cache
                $meta['cache'] = $this->reader->getClassAnnotation($reflectionClass, 'Timiki\Bundle\RpcServerBundle\Mapping\Cache');

                // Roles
                $meta['roles'] = $this->reader->getClassAnnotation($reflectionClass, 'Timiki\Bundle\RpcServerBundle\Mapping\Roles');

                // Method execute. On in class
                $meta['executeMethod'] = null;

                foreach ($reflectionClass->getMethods() as $reflectionMethod) {
                    if ($paramMeta = $this->reader->getMethodAnnotation($reflectionMethod, 'Timiki\Bundle\RpcServerBundle\Mapping\Execute')) {
                        $meta['executeMethod'] = $reflectionMethod->name;
                    }
                }

                if (empty($meta['executeMethod'])) {
                    throw new InvalidMappingException(
                        sprintf(
                            'Method need have @Execute annotation in class "%s", @Execute()',
                            $class
                        )
                    );
                }

                // Params
                $meta['params'] = [];

                foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                    if ($paramMeta = $this->reader->getPropertyAnnotation($reflectionProperty, 'Timiki\Bundle\RpcServerBundle\Mapping\Param')) {
                        $meta['params'][$reflectionProperty->name] = $paramMeta;
                    }
                }

            }

        }

        return $meta;
    }

    /**
     * Load RPC method metadata from file.
     *
     * @param string $file Rpc method file
     * @return array|null
     * @throws InvalidMappingException
     */
    public function loadFileMetadata($file)
    {
        if (file_exists($file)) {

            // If file already process?
            if (array_key_exists($file, self::$loadFiles)) {
                return self::$loadFiles[$file];
            }

            $meta = [];

            $classes = get_declared_classes();

            include_once $file;

            // Process find class in file, foreach for extend
            foreach (array_diff(get_declared_classes(), $classes) as $class) {

                if ($data = $this->loadClassMetadata($class)) {
                    $meta[] = $data;
                }

            }

            self::$loadFiles[$file] = $meta;

            return $meta;
        }

        return null;
    }
}
