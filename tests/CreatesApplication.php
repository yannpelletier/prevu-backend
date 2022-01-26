<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $createApp = function() {
            $app = require __DIR__.'/../bootstrap/app.php';
            $app->make(Kernel::class)->bootstrap();
            return $app;
        };

        $app = $createApp();
        if ($app->environment() !== 'testing') {
            $this->clearCache();
            $app = $createApp();
        }

        return $app;
    }
    /**
     * Clears Laravel Cache.
     */
    protected function clearCache()
    {
        $commands = ['clear-compiled', 'cache:clear', 'view:clear', 'config:clear', 'route:clear'];
        foreach ($commands as $command) {
            \Illuminate\Support\Facades\Artisan::call($command);
        }
    }
}
