<?php

namespace Timiki\Bundle\RpcServerBundle\Server;

use Timiki\Bundle\RpcServerBundle\Server\Traits\CacheTrait;
use Timiki\Bundle\RpcServerBundle\Server\Exceptions\InvalidMappingException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\DocParser;

/**
 * RPC Mapper.
 *
 * Service for found RPC methods in bundles and mapping methods metadata.
 */
class Mapper implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    use CacheTrait;

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
     * @var boolean
     */
    protected $debug;

    /**
     * @var array
     */
    protected static $loadFiles = [];

    /**
     * @param bool $debug
     */
    public function __construct($debug = false)
    {
        $this->reader = new AnnotationReader(new DocParser());
        $this->debug  = $debug;
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
     * @return $this
     */
    public function addPath($path)
    {
        if (is_dir($path)) {
            $this->paths[] = $path;
        }

        return $this;
    }

    /**
     * Add file for mapping rpc methods.
     *
     * @param string $file
     * @return $this
     * @throws \Timiki\Bundle\RpcServerBundle\Server\Exceptions\InvalidMappingException
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
     * @return array
     * @throws InvalidMappingException
     */
    public function loadMetadata()
    {

        if (!empty($this->meta)) {
            return $this->meta;
        }

        if ($this->cache && !$this->debug && $meta = $this->cache->fetch('rpc.meta')) {

            $this->meta = $meta;

            return $meta;
        }

        if ($this->container && $this->container->has('debug.stopwatch')) {
            $stopwatch = $this->container->get('debug.stopwatch');
            $stopwatch->start('rpc.mapping');
        }

        $meta = [];

        // Process files
        foreach ($this->files as $file) {
            if ($methodMeta = $this->loadFileMetadata($file)) {
                $meta[$methodMeta['class']] = $methodMeta;
            }
        }

        // Process dirs
        foreach ($this->paths as $path) {
            $meta += $this->loadPathMetadata($path);
        }

        if ($this->cache) {
            $this->cache->save('rpc.meta', $meta);
        }

        $this->meta = $meta;

        if (isset($stopwatch)) {
            $stopwatch->stop('rpc.mapping');
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
    public function loadPathMetadata($path)
    {
        $meta = [];

        if (is_dir($path)) {

            $dir = new \DirectoryIterator($path);

            foreach ($dir as $file) {


                if ($file->isFile()) {

                    if ($methodMeta = $this->loadFileMetadata($file->getRealPath())) {

                        $meta[$methodMeta['class']] = $methodMeta;

                    }

                }

                if ($file->isDir() && !$file->isDot()) {
                    $this->loadPathMetadata($file->getRealPath());
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

            if ($method = $this->reader->getClassAnnotation($reflectionClass,
                'Timiki\Bundle\RpcServerBundle\Mapping\Method')
            ) {

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
                $meta['cache'] = $this->reader->getClassAnnotation($reflectionClass,
                    'Timiki\Bundle\RpcServerBundle\Mapping\Cache');

                // Roles
                $meta['roles'] = $this->reader->getClassAnnotation($reflectionClass,
                    'Timiki\Bundle\RpcServerBundle\Mapping\Roles');

                // Method execute. On in class
                $meta['executeMethod'] = null;

                foreach ($reflectionClass->getMethods() as $reflectionMethod) {

                    if ($paramMeta = $this->reader->getMethodAnnotation($reflectionMethod,
                        'Timiki\Bundle\RpcServerBundle\Mapping\Execute')
                    ) {

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

                    if ($paramMeta = $this->reader->getPropertyAnnotation($reflectionProperty,
                        'Timiki\Bundle\RpcServerBundle\Mapping\Param')
                    ) {

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
                $meta = $this->loadClassMetadata($class);
            }

            self::$loadFiles[$file] = $meta;

            return $meta;
        }

        return null;
    }
}
