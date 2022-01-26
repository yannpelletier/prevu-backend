# Prev-U Backend

## Projet setup (development only)
#### Xampp configuration
Modify the apache configuration file to run the backend development server on port 5000. Port 5000 is used to allow port forwarding while using USB debugging in chrome devtools.

Modify the following lines in `C:\xampp\apache\conf`:
```
Listen 80
# ...
ServerName localhost:80
```
To:
```
Listen 5000
# ...
ServerName localhost:5000
```

#### Configuration for mobile web debugging
- Activate the developer mode on your Android devide.
- Go to settings -> Developer mode options. Enable USB debugging (do it once). Then, in networking, choose the USB configuration to MIDI (to do each time).
- Open chrome devtools. Go to More tools -> Remote devices. Enable USB device discovery and Port forwarding.
- Forward the device port 5000 to the local address localhost:5000.
- Forward the device port 8080 to the local address localhost:8080.

You should then be able to use the web app in dev mode with hot reload & debug it.

#### Install dependencies & migrate cache
```
composer install
php artisan migrate
```

#### Rollback, migrate and seed the database
```
php artisan migrate:refresh --seed
```

#### Create configuration cache
This is required whenever running the application in order to avoid errors when multiple users connect to the application.
If not cached, some environment variables will seem to be non-existent to some users.
This is known to cause a bug when making multiple simultaneous requests to the server. Users may experience 503 server errors.
```
php artisan config:cache
```

#### Create passport token keys
This is required after a database rollback or when the table `oauth_clients` is empty.
```
php artisan passport:install
```

#### Clear autoloader cache
```
composer dump-autoload
```

#### Launch worker thread
```
php artisan queue:work
```

#### Debugging
You can use Xdebug in order to debug tests. Add the following line to your php.ini file :

```
zend_extension = "[Path to PHP]/ext/php_xdebug.dll"
```

## Testing
To perform tests, make sure that
- You have phpunit installed and added to the path.
#### Backend testing
To perform tests, make sure that
- Have mysqli installed and added to the path.
- You must modify the following file :
`vendor/laravel/framework/src/Illuminate/Filesystem/FilesystemAdapter.php`
by adding the following code at the beginning of the method `temporaryUrl`:
```
if (env('APP_ENV') === 'testing') {
    return $path;
}
```
Before each test, the configuration cache will be automatically cleared to use test environment variables. 
When you want to switch back to the development environment, you will need to manually cache your configuration:
```
php artisan config:cache
```
#### Frontend testing (Laravel Dusk)
To perform tests, make sure that
- You have installed the latest version of Google Chrome
- You have installed the latest version of Chrome Driver:

```
php artisan dusk:chrome-driver
```

## Building for production
This will create the zip archive for AWS Elastic Beanstalk.

Make sure you have built the Vue.js first.



````
php artisan build
````

This should create Prev-U_build.zip.
You can then upload this build to AWS ElasticBeanstalk.
