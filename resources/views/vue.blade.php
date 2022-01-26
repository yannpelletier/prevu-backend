<?php
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;

Bugsnag::notifyException(new RuntimeException("Test error"));
?>
<h1>Hey! You need to compile the Vue.JS first!</h1>
<p>To do so, vue.blade.php (this file) needs to be replaced by [vue-project]/dist/index.html</p>
<p>And all the dependencies (css, js, media folders) go in [laravel-project]/public</p>
<p><b>php artisan build</b> will do so.</p>
-Freüd
