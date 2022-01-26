<?php


namespace App\OverridenPackages\Filesystem;

use Aws\S3\S3Client;
use Illuminate\Filesystem\FilesystemManager as LaravelFilesystemManager;
use Illuminate\Support\Arr;
use League\Flysystem\Adapter\Ftp as FtpAdapter;
use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\AdapterInterface;
use League\Flysystem\AwsS3v3\AwsS3Adapter as S3Adapter;
use App\OverridenPackages\Filesystem\Filesystem as Flysystem;
use League\Flysystem\FilesystemInterface;
use Illuminate\Filesystem\Cache;
use League\Flysystem\Cached\CacheInterface;

class FilesystemManager extends LaravelFilesystemManager
{
    public function __construct($app)
    {
        parent::__construct($app);
    }

    /**
     * Adapt the filesystem implementation.
     *
     * @param  \League\Flysystem\FilesystemInterface  $filesystem
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected function adapt(FilesystemInterface $filesystem)
    {
        return new FilesystemAdapter($filesystem);
    }
    /**
     * Get a filesystem instance.
     *
     * @param  string|null  $name
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function drive($name = null)
    {
        return $this->disk($name);
    }

    /**
     * Get a filesystem instance.
     *
     * @param  string|null  $name
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function disk($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->disks[$name] = $this->get($name);
    }

    /**
     * Get a default cloud filesystem instance.
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function cloud()
    {
        $name = $this->getDefaultCloudDriver();

        return $this->disks[$name] = $this->get($name);
    }

    /**
     * Attempt to get the disk from the local cache.
     *
     * @param  string  $name
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected function get($name)
    {
        return $this->disks[$name] ?? $this->resolve($name);
    }

    /**
     * Resolve the given disk.
     *
     * @param  string  $name
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (empty($config['driver'])) {
            throw new InvalidArgumentException("Disk [{$name}] does not have a configured driver.");
        }

        $name = $config['driver'];

        if (isset($this->customCreators[$name])) {
            return $this->callCustomCreator($config);
        }

        $driverMethod = 'create'.ucfirst($name).'Driver';

        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($config);
        } else {
            throw new InvalidArgumentException("Driver [{$name}] is not supported.");
        }
    }

    /**
     * Call a custom driver creator.
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected function callCustomCreator(array $config)
    {
        $driver = $this->customCreators[$config['driver']]($this->app, $config);

        if ($driver instanceof FilesystemInterface) {
            return $this->adapt($driver);
        }

        return $driver;
    }

    /**
     * Create an instance of the local driver.
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function createLocalDriver(array $config)
    {
        $permissions = $config['permissions'] ?? [];

        $links = ($config['links'] ?? null) === 'skip'
            ? LocalAdapter::SKIP_LINKS
            : LocalAdapter::DISALLOW_LINKS;

        return $this->adapt($this->createFlysystem(new LocalAdapter(
            $config['root'], $config['lock'] ?? LOCK_EX, $links, $permissions
        ), $config));
    }

    /**
     * Create an instance of the ftp driver.
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function createFtpDriver(array $config)
    {
        return $this->adapt($this->createFlysystem(
            new FtpAdapter($config), $config
        ));
    }

    /**
     * Create an instance of the sftp driver.
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function createSftpDriver(array $config)
    {
        return $this->adapt($this->createFlysystem(
            new SftpAdapter($config), $config
        ));
    }

    /**
     * Create an instance of the Amazon S3 driver.
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Filesystem\Cloud
     */
    public function createS3Driver(array $config)
    {
        $s3Config = $this->formatS3Config($config);

        $root = $s3Config['root'] ?? null;

        $options = $config['options'] ?? [];

        return $this->adapt($this->createFlysystem(
            new S3Adapter(new S3Client($s3Config), $s3Config['bucket'], $root, $options), $config
        ));
    }

    /**
     * Format the given S3 configuration with the default options.
     *
     * @param  array  $config
     * @return array
     */
    protected function formatS3Config(array $config)
    {
        $config += ['version' => 'latest'];

        if (! empty($config['key']) && ! empty($config['secret'])) {
            $config['credentials'] = Arr::only($config, ['key', 'secret', 'token']);
        }

        return $config;
    }

    /**
     * Create a Flysystem instance with the given adapter.
     *
     * @param  \League\Flysystem\AdapterInterface  $adapter
     * @param  array  $config
     * @return \League\Flysystem\FilesystemInterface
     */
    protected function createFlysystem(AdapterInterface $adapter, array $config)
    {
        $cache = Arr::pull($config, 'cache');

        $config = Arr::only($config, ['visibility', 'disable_asserts', 'url']);

        if ($cache) {
            $adapter = new CachedAdapter($adapter, $this->createCacheStore($cache));
        }

        return new Flysystem($adapter, count($config) > 0 ? $config : null);
    }

    /**
     * Create a cache store instance.
     *
     * @param  mixed  $config
     * @return \League\Flysystem\Cached\CacheInterface
     *
     * @throws \InvalidArgumentException
     */
    protected function createCacheStore($config)
    {
        if ($config === true) {
            return new MemoryStore;
        }

        return new Cache(
            $this->app['cache']->store($config['store']),
            $config['prefix'] ?? 'flysystem',
            $config['expire'] ?? null
        );
    }

    /**
     * Set the given disk instance.
     *
     * @param  string  $name
     * @param  mixed  $disk
     * @return $this
     */
    public function set($name, $disk)
    {
        $this->disks[$name] = $disk;

        return $this;
    }

    /**
     * Get the filesystem connection configuration.
     *
     * @param  string  $name
     * @return array
     */
    protected function getConfig($name)
    {
        return $this->app['config']["filesystems.disks.{$name}"] ?: [];
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['filesystems.default'];
    }

    /**
     * Get the default cloud driver name.
     *
     * @return string
     */
    public function getDefaultCloudDriver()
    {
        return $this->app['config']['filesystems.cloud'];
    }

    /**
     * Unset the given disk instances.
     *
     * @param  array|string  $disk
     * @return $this
     */
    public function forgetDisk($disk)
    {
        foreach ((array) $disk as $diskName) {
            unset($this->disks[$diskName]);
        }

        return $this;
    }

    /**
     * Register a custom driver creator Closure.
     *
     * @param  string  $driver
     * @param  \Closure  $callback
     * @return $this
     */
    public function extend($driver, Closure $callback)
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->disk()->$method(...$parameters);
    }

    /**
     * Determine if a file exists.
     *
     * @param string $path
     * @return bool
     */
    public function exists($path)
    {
        return parent::exists($path);
    }

    /**
     * Get a resource to read the file.
     *
     * @param string $path
     * @return resource|null The path resource or null on failure.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function readStream($path)
    {
        return parent::readStream($path);
    }

    /**
     * Write the contents of a file.
     *
     * @param string $path
     * @param string|resource $contents
     * @param mixed $options
     * @return bool
     */
    public function put($path, $contents, $options = [])
    {
        return parent::put($path, $contents, $options);
    }

    /**
     * Write a new file using a stream.
     *
     * @param string $path
     * @param resource $resource
     * @param array $options
     * @return bool
     *
     * @throws \InvalidArgumentException If $resource is not a file handle.
     * @throws \Illuminate\Contracts\Filesystem\FileExistsException
     */
    public function writeStream($path, $resource, array $options = [])
    {
        return parent::writeStream($path, $resource, $options);
    }

    /**
     * Get the visibility for the given path.
     *
     * @param string $path
     * @return string
     */
    public function getVisibility($path)
    {
        return parent::getVisibility($path);
    }

    /**
     * Set the visibility for the given path.
     *
     * @param string $path
     * @param string $visibility
     * @return bool
     */
    public function setVisibility($path, $visibility)
    {
        return parent::setVisibility($path, $visibility);
    }

    /**
     * Prepend to a file.
     *
     * @param string $path
     * @param string $data
     * @return bool
     */
    public function prepend($path, $data)
    {
        return parent::prepend($path, $data);
    }

    /**
     * Append to a file.
     *
     * @param string $path
     * @param string $data
     * @return bool
     */
    public function append($path, $data)
    {
        return parent::append($path, $data);
    }

    /**
     * Delete the file at a given path.
     *
     * @param string|array $paths
     * @return bool
     */
    public function delete($paths)
    {
        return parent::delete($paths);
    }

    /**
     * Copy a file to a new location.
     *
     * @param string $from
     * @param string $to
     * @return bool
     */
    public function copy($from, $to)
    {
        return parent::copy($from, $to);
    }

    /**
     * Move a file to a new location.
     *
     * @param string $from
     * @param string $to
     * @return bool
     */
    public function move($from, $to)
    {
        return parent::move($from, $to);
    }

    /**
     * Get the file size of a given file.
     *
     * @param string $path
     * @return int
     */
    public function size($path)
    {
        return parent::size($path);
    }

    /**
     * Get the file's last modification time.
     *
     * @param string $path
     * @return int
     */
    public function lastModified($path)
    {
        return parent::lastModified($path);
    }

    /**
     * Get an array of all files in a directory.
     *
     * @param string|null $directory
     * @param bool $recursive
     * @return array
     */
    public function files($directory = null, $recursive = false)
    {
        return parent::files($directory, $recursive);
    }

    /**
     * Get all of the files from the given directory (recursive).
     *
     * @param string|null $directory
     * @return array
     */
    public function allFiles($directory = null)
    {
        return parent::allFiles($directory);
    }

    /**
     * Get all of the directories within a given directory.
     *
     * @param string|null $directory
     * @param bool $recursive
     * @return array
     */
    public function directories($directory = null, $recursive = false)
    {
        return parent::directories($directory, $recursive);
    }

    /**
     * Get all (recursive) of the directories within a given directory.
     *
     * @param string|null $directory
     * @return array
     */
    public function allDirectories($directory = null)
    {
        return parent::allDirectories($directory);
    }

    /**
     * Create a directory.
     *
     * @param string $path
     * @return bool
     */
    public function makeDirectory($path)
    {
        return parent::makeDirectory($path);
    }

    /**
     * Recursively delete a directory.
     *
     * @param string $directory
     * @return bool
     */
    public function deleteDirectory($directory)
    {
        return parent::deleteDirectory($directory);
    }
}
