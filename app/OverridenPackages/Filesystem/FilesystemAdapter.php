<?php


namespace App\OverridenPackages\Filesystem;

use Illuminate\Contracts\Filesystem\FileExistsException as ContractFileExistsException;
use Illuminate\Contracts\Filesystem\FileNotFoundException as ContractFileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem as FilesystemContract;
use Illuminate\Filesystem\FilesystemAdapter as LaravelFilesystemAdapter;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\AdapterInterface;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\PluginInterface;
use League\Flysystem\RootViolationException;
use PHPUnit\Framework\Assert as PHPUnit;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @mixin \League\Flysystem\FilesystemInterface
 */
class FilesystemAdapter extends LaravelFilesystemAdapter
{
    public function __construct($app)
    {
        parent::__construct($app);
    }


    /**
     * Assert that the given file exists.
     *
     * @param  string|array  $path
     * @return $this
     */
    public function assertExists($path)
    {
        $paths = Arr::wrap($path);

        foreach ($paths as $path) {
            PHPUnit::assertTrue(
                $this->exists($path), "Unable to find a file at path [{$path}]."
            );
        }

        return $this;
    }

    /**
     * Assert that the given file does not exist.
     *
     * @param  string|array  $path
     * @return $this
     */
    public function assertMissing($path)
    {
        $paths = Arr::wrap($path);

        foreach ($paths as $path) {
            PHPUnit::assertFalse(
                $this->exists($path), "Found unexpected file at path [{$path}]."
            );
        }

        return $this;
    }

    /**
     * Determine if a file exists.
     *
     * @param  string  $path
     * @return bool
     */
    public function exists($path)
    {
        return $this->driver->has($path);
    }

    /**
     * Determine if a file or directory is missing.
     *
     * @param  string  $path
     * @return bool
     */
    public function missing($path)
    {
        return ! $this->exists($path);
    }

    /**
     * Get the full path for the file at the given "short" path.
     *
     * @param  string  $path
     * @return string
     */
    public function path($path)
    {
        return $this->driver->getAdapter()->getPathPrefix().$path;
    }

    /**
     * Get the contents of a file.
     *
     * @param  string  $path
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function get($path)
    {
        try {
            return $this->driver->read($path);
        } catch (FileNotFoundException $e) {
            throw new ContractFileNotFoundException($path, $e->getCode(), $e);
        }
    }

    /**
     * Create a streamed response for a given file.
     *
     * @param  string  $path
     * @param  string|null  $name
     * @param  array|null  $headers
     * @param  string|null  $disposition
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function response($path, $name = null, array $headers = [], $disposition = 'inline')
    {
        $response = new StreamedResponse;

        $filename = $name ?? basename($path);

        $disposition = $response->headers->makeDisposition(
            $disposition, $filename, $this->fallbackName($filename)
        );

        $response->headers->replace($headers + [
                'Content-Type' => $this->mimeType($path),
                'Content-Length' => $this->size($path),
                'Content-Disposition' => $disposition,
            ]);

        $response->setCallback(function () use ($path) {
            $stream = $this->readStream($path);
            fpassthru($stream);
            fclose($stream);
        });

        return $response;
    }

    /**
     * Create a streamed download response for a given file.
     *
     * @param  string  $path
     * @param  string|null  $name
     * @param  array|null  $headers
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download($path, $name = null, array $headers = [])
    {
        return $this->response($path, $name, $headers, 'attachment');
    }

    /**
     * Convert the string to ASCII characters that are equivalent to the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function fallbackName($name)
    {
        return str_replace('%', '', Str::ascii($name));
    }

    /**
     * Write the contents of a file.
     *
     * @param  string  $path
     * @param  string|resource  $contents
     * @param  mixed  $options
     * @return bool
     */
    public function put($path, $contents, $options = [])
    {
        $options = is_string($options)
            ? ['visibility' => $options]
            : (array) $options;

        // If the given contents is actually a file or uploaded file instance than we will
        // automatically store the file using a stream. This provides a convenient path
        // for the developer to store streams without managing them manually in code.
        if ($contents instanceof File ||
            $contents instanceof UploadedFile) {
            return $this->putFile($path, $contents, $options);
        }

        if ($contents instanceof StreamInterface) {
            return $this->driver->putStream($path, $contents->detach(), $options);
        }

        return is_resource($contents)
            ? $this->driver->putStream($path, $contents, $options)
            : $this->driver->put($path, $contents, $options);
    }

    /**
     * Store the uploaded file on the disk.
     *
     * @param  string  $path
     * @param  \Illuminate\Http\File|\Illuminate\Http\UploadedFile  $file
     * @param  array  $options
     * @return string|false
     */
    public function putFile($path, $file, $options = [])
    {
        return $this->putFileAs($path, $file, $file->hashName(), $options);
    }

    /**
     * Store the uploaded file on the disk with a given name.
     *
     * @param  string  $path
     * @param  \Illuminate\Http\File|\Illuminate\Http\UploadedFile  $file
     * @param  string  $name
     * @param  array  $options
     * @return string|false
     */
    public function putFileAs($path, $file, $name, $options = [])
    {
        $stream = fopen($file->getRealPath(), 'r');

        // Next, we will format the path of the file and store the file using a stream since
        // they provide better performance than alternatives. Once we write the file this
        // stream will get closed automatically by us so the developer doesn't have to.
        $result = $this->put(
            $path = trim($path.'/'.$name, '/'), $stream, $options
        );

        if (is_resource($stream)) {
            fclose($stream);
        }

        return $result ? $path : false;
    }

    /**
     * Get the visibility for the given path.
     *
     * @param  string  $path
     * @return string
     */
    public function getVisibility($path)
    {
        if ($this->driver->getVisibility($path) == AdapterInterface::VISIBILITY_PUBLIC) {
            return FilesystemContract::VISIBILITY_PUBLIC;
        }

        return FilesystemContract::VISIBILITY_PRIVATE;
    }

    /**
     * Set the visibility for the given path.
     *
     * @param  string  $path
     * @param  string  $visibility
     * @return bool
     */
    public function setVisibility($path, $visibility)
    {
        return $this->driver->setVisibility($path, $this->parseVisibility($visibility));
    }

    /**
     * Prepend to a file.
     *
     * @param  string  $path
     * @param  string  $data
     * @param  string  $separator
     * @return bool
     */
    public function prepend($path, $data, $separator = PHP_EOL)
    {
        if ($this->exists($path)) {
            return $this->put($path, $data.$separator.$this->get($path));
        }

        return $this->put($path, $data);
    }

    /**
     * Append to a file.
     *
     * @param  string  $path
     * @param  string  $data
     * @param  string  $separator
     * @return bool
     */
    public function append($path, $data, $separator = PHP_EOL)
    {
        if ($this->exists($path)) {
            return $this->put($path, $this->get($path).$separator.$data);
        }

        return $this->put($path, $data);
    }

    /**
     * Delete the file at a given path.
     *
     * @param  string|array  $paths
     * @return bool
     */
    public function delete($paths)
    {
        $paths = is_array($paths) ? $paths : func_get_args();

        $success = true;

        foreach ($paths as $path) {
            try {
                if (! $this->driver->delete($path)) {
                    $success = false;
                }
            } catch (FileNotFoundException $e) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Copy a file to a new location.
     *
     * @param  string  $from
     * @param  string  $to
     * @return bool
     */
    public function copy($from, $to)
    {
        return $this->driver->copy($from, $to);
    }

    /**
     * Move a file to a new location.
     *
     * @param  string  $from
     * @param  string  $to
     * @return bool
     */
    public function move($from, $to)
    {
        return $this->driver->rename($from, $to);
    }

    /**
     * Get the file size of a given file.
     *
     * @param  string  $path
     * @return int
     */
    public function size($path)
    {
        return $this->driver->getSize($path);
    }

    /**
     * Get the mime-type of a given file.
     *
     * @param  string  $path
     * @return string|false
     */
    public function mimeType($path)
    {
        return $this->driver->getMimetype($path);
    }

    /**
     * Get the file's last modification time.
     *
     * @param  string  $path
     * @return int
     */
    public function lastModified($path)
    {
        return $this->driver->getTimestamp($path);
    }

    /**
     * Get the URL for the file at the given path.
     *
     * @param  string  $path
     * @return string
     *
     * @throws \RuntimeException
     */
    public function url($path)
    {
        $adapter = $this->driver->getAdapter();

        if ($adapter instanceof CachedAdapter) {
            $adapter = $adapter->getAdapter();
        }

        if (method_exists($adapter, 'getUrl')) {
            return $adapter->getUrl($path);
        } elseif (method_exists($this->driver, 'getUrl')) {
            return $this->driver->getUrl($path);
        } elseif ($adapter instanceof AwsS3Adapter) {
            return $this->getAwsUrl($adapter, $path);
        } elseif ($adapter instanceof LocalAdapter) {
            return $this->getLocalUrl($path);
        } else {
            throw new RuntimeException('This driver does not support retrieving URLs.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
        try {
            return $this->driver->readStream($path) ?: null;
        } catch (FileNotFoundException $e) {
            throw new ContractFileNotFoundException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream($path, $resource, array $options = [])
    {
        try {
            return $this->driver->writeStream($path, $resource, $options);
        } catch (FileExistsException $e) {
            throw new ContractFileExistsException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Get the URL for the file at the given path.
     *
     * @param  \League\Flysystem\AwsS3v3\AwsS3Adapter  $adapter
     * @param  string  $path
     * @return string
     */
    protected function getAwsUrl($adapter, $path)
    {
        // If an explicit base URL has been set on the disk configuration then we will use
        // it as the base URL instead of the default path. This allows the developer to
        // have full control over the base path for this filesystem's generated URLs.
        if (! is_null($url = $this->driver->getConfig()->get('url'))) {
            return $this->concatPathToUrl($url, $adapter->getPathPrefix().$path);
        }

        return $adapter->getClient()->getObjectUrl(
            $adapter->getBucket(), $adapter->getPathPrefix().$path
        );
    }

    /**
     * Get the URL for the file at the given path.
     *
     * @param  string  $path
     * @return string
     */
    protected function getLocalUrl($path)
    {
        $config = $this->driver->getConfig();

        // If an explicit base URL has been set on the disk configuration then we will use
        // it as the base URL instead of the default path. This allows the developer to
        // have full control over the base path for this filesystem's generated URLs.
        if ($config->has('url')) {
            return $this->concatPathToUrl($config->get('url'), $path);
        }

        $path = '/storage/'.$path;

        // If the path contains "storage/public", it probably means the developer is using
        // the default disk to generate the path instead of the "public" disk like they
        // are really supposed to use. We will remove the public from this path here.
        if (Str::contains($path, '/storage/public/')) {
            return Str::replaceFirst('/public/', '/', $path);
        }

        return $path;
    }

    /**
     * Get a temporary URL for the file at the given path.
     *
     * @param  string  $path
     * @param  \DateTimeInterface  $expiration
     * @param  array  $options
     * @return string
     *
     * @throws \RuntimeException
     */
    public function temporaryUrl($path, $expiration, array $options = [])
    {
        $adapter = $this->driver->getAdapter();

        if ($adapter instanceof CachedAdapter) {
            $adapter = $adapter->getAdapter();
        }

        if (method_exists($adapter, 'getTemporaryUrl')) {
            return $adapter->getTemporaryUrl($path, $expiration, $options);
        } elseif ($adapter instanceof AwsS3Adapter) {
            return $this->getAwsTemporaryUrl($adapter, $path, $expiration, $options);
        } else {
            throw new RuntimeException('This driver does not support creating temporary URLs.');
        }
    }

    /**
     * Get a temporary URL for the file at the given path.
     *
     * @param  \League\Flysystem\AwsS3v3\AwsS3Adapter  $adapter
     * @param  string  $path
     * @param  \DateTimeInterface  $expiration
     * @param  array  $options
     * @return string
     */
    public function getAwsTemporaryUrl($adapter, $path, $expiration, $options)
    {
        $client = $adapter->getClient();

        $command = $client->getCommand('GetObject', array_merge([
            'Bucket' => $adapter->getBucket(),
            'Key' => $adapter->getPathPrefix().$path,
        ], $options));

        return (string) $client->createPresignedRequest(
            $command, $expiration
        )->getUri();
    }

    /**
     * Concatenate a path to a URL.
     *
     * @param  string  $url
     * @param  string  $path
     * @return string
     */
    protected function concatPathToUrl($url, $path)
    {
        return rtrim($url, '/').'/'.ltrim($path, '/');
    }

    /**
     * Get an array of all files in a directory.
     *
     * @param  string|null  $directory
     * @param  bool  $recursive
     * @return array
     */
    public function files($directory = null, $recursive = false)
    {
        $contents = $this->driver->listContents($directory, $recursive);

        return $this->filterContentsByType($contents, 'file');
    }

    /**
     * Get all of the files from the given directory (recursive).
     *
     * @param  string|null  $directory
     * @return array
     */
    public function allFiles($directory = null)
    {
        return $this->files($directory, true);
    }

    /**
     * Get all of the directories within a given directory.
     *
     * @param  string|null  $directory
     * @param  bool  $recursive
     * @return array
     */
    public function directories($directory = null, $recursive = false)
    {
        $contents = $this->driver->listContents($directory, $recursive);

        return $this->filterContentsByType($contents, 'dir');
    }

    /**
     * Get all (recursive) of the directories within a given directory.
     *
     * @param  string|null  $directory
     * @return array
     */
    public function allDirectories($directory = null)
    {
        return $this->directories($directory, true);
    }

    /**
     * Create a directory.
     *
     * @param  string  $path
     * @return bool
     */
    public function makeDirectory($path)
    {
        return $this->driver->createDir($path);
    }

    /**
     * Recursively delete a directory.
     *
     * @param  string  $directory
     * @return bool
     */
    public function deleteDirectory($directory)
    {
        return $this->driver->deleteDir($directory);
    }

    /**
     * Flush the Flysystem cache.
     *
     * @return void
     */
    public function flushCache()
    {
        $adapter = $this->driver->getAdapter();

        if ($adapter instanceof CachedAdapter) {
            $adapter->getCache()->flush();
        }
    }

    /**
     * Get the Flysystem driver.
     *
     * @return \League\Flysystem\FilesystemInterface
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Filter directory contents by type.
     *
     * @param  array  $contents
     * @param  string  $type
     * @return array
     */
    protected function filterContentsByType($contents, $type)
    {
        return Collection::make($contents)
            ->where('type', $type)
            ->pluck('path')
            ->values()
            ->all();
    }

    /**
     * Parse the given visibility value.
     *
     * @param  string|null  $visibility
     * @return string|null
     *
     * @throws \InvalidArgumentException
     */
    protected function parseVisibility($visibility)
    {
        if (is_null($visibility)) {
            return;
        }

        switch ($visibility) {
            case FilesystemContract::VISIBILITY_PUBLIC:
                return AdapterInterface::VISIBILITY_PUBLIC;
            case FilesystemContract::VISIBILITY_PRIVATE:
                return AdapterInterface::VISIBILITY_PRIVATE;
        }

        throw new InvalidArgumentException("Unknown visibility: {$visibility}");
    }

    /**
     * Pass dynamic methods call onto Flysystem.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, array $parameters)
    {
        return call_user_func_array([$this->driver, $method], $parameters);
    }
    /**
     * Check whether a file exists.
     *
     * @param string $path
     *
     * @return bool
     */
    public function has($path)
    {
        return parent::has($path);
    }

    /**
     * Read a file.
     *
     * @param string $path The path to the file.
     *
     * @return string|false The file contents or false on failure.
     * @throws FileNotFoundException
     *
     */
    public function read($path)
    {
        return parent::read($path);
    }

    /**
     * List contents of a directory.
     *
     * @param string $directory The directory to list.
     * @param bool $recursive Whether to list recursively.
     *
     * @return array A list of file metadata.
     */
    public function listContents($directory = '', $recursive = false)
    {
        return parent::listContents($directory, $recursive);
    }

    /**
     * Get a file's metadata.
     *
     * @param string $path The path to the file.
     *
     * @return array|false The file metadata or false on failure.
     * @throws FileNotFoundException
     *
     */
    public function getMetadata($path)
    {
        return parent::getMetadata($path);
    }

    /**
     * Get a file's size.
     *
     * @param string $path The path to the file.
     *
     * @return int|false The file size or false on failure.
     * @throws FileNotFoundException
     *
     */
    public function getSize($path)
    {
        return parent::getSize($path);
    }

    /**
     * Get a file's mime-type.
     *
     * @param string $path The path to the file.
     *
     * @return string|false The file mime-type or false on failure.
     * @throws FileNotFoundException
     *
     */
    public function getMimetype($path)
    {
        return parent::getMimetype($path);
    }

    /**
     * Get a file's timestamp.
     *
     * @param string $path The path to the file.
     *
     * @return string|false The timestamp or false on failure.
     * @throws FileNotFoundException
     *
     */
    public function getTimestamp($path)
    {
        return parent::getTimestamp($path);
    }

    /**
     * Write a new file.
     *
     * @param string $path The path of the new file.
     * @param string $contents The file contents.
     * @param array $config An optional configuration array.
     *
     * @return bool True on success, false on failure.
     * @throws FileExistsException
     *
     */
    public function write($path, $contents, array $config = [])
    {
        return parent::write($path, $contents, $config);
    }

    /**
     * Update an existing file.
     *
     * @param string $path The path of the existing file.
     * @param string $contents The file contents.
     * @param array $config An optional configuration array.
     *
     * @return bool True on success, false on failure.
     * @throws FileNotFoundException
     *
     */
    public function update($path, $contents, array $config = [])
    {
        return parent::update($path, $contents, $config);
    }

    /**
     * Update an existing file using a stream.
     *
     * @param string $path The path of the existing file.
     * @param resource $resource The file handle.
     * @param array $config An optional configuration array.
     *
     * @return bool True on success, false on failure.
     * @throws FileNotFoundException
     *
     * @throws InvalidArgumentException If $resource is not a file handle.
     */
    public function updateStream($path, $resource, array $config = [])
    {
        return parent::updateStream($path, $resource, $config);
    }

    /**
     * Rename a file.
     *
     * @param string $path Path to the existing file.
     * @param string $newpath The new path of the file.
     *
     * @return bool True on success, false on failure.
     * @throws FileNotFoundException Thrown if $path does not exist.
     *
     * @throws FileExistsException   Thrown if $newpath exists.
     */
    public function rename($path, $newpath)
    {
        return parent::rename($path, $newpath);
    }

    /**
     * Delete a directory.
     *
     * @param string $dirname
     *
     * @return bool True on success, false on failure.
     * @throws RootViolationException Thrown if $dirname is empty.
     *
     */
    public function deleteDir($dirname)
    {
        return parent::deleteDir($dirname);
    }

    /**
     * Create a directory.
     *
     * @param string $dirname The name of the new directory.
     * @param array $config An optional configuration array.
     *
     * @return bool True on success, false on failure.
     */
    public function createDir($dirname, array $config = [])
    {
        return parent::createDir($dirname, $config);
    }

    /**
     * Create a file or update if exists.
     *
     * @param string $path The path to the file.
     * @param resource $resource The file handle.
     * @param array $config An optional configuration array.
     *
     * @return bool True on success, false on failure.
     * @throws InvalidArgumentException Thrown if $resource is not a resource.
     *
     */
    public function putStream($path, $resource, array $config = [])
    {
        return parent::putStream($path, $resource, $config);
    }

    /**
     * Read and delete a file.
     *
     * @param string $path The path to the file.
     *
     * @return string|false The file contents, or false on failure.
     * @throws FileNotFoundException
     *
     */
    public function readAndDelete($path)
    {
        return parent::readAndDelete($path);
    }

    /**
     * Register a plugin.
     *
     * @param PluginInterface $plugin The plugin to register.
     *
     * @return $this
     */
    public function addPlugin(PluginInterface $plugin)
    {
        parent::addPlugin($plugin);
    }
}
