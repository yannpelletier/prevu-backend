<?php

namespace App\Jobs\Thumbnails;

use App\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Filesystem\Filesystem;

abstract class CompileFileThumbnail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $source;
    public $destination;
    public $private;

    /**
     * Create a new job instance.
     **
     * @param string $source
     * @param string $destination
     * @param string $visibility
     */
    public function __construct(string $source, string $destination, bool $private)
    {
        $this->source = $source;
        $this->destination = $destination;
        $this->private = $private;
      }

    /**
     * Execute the job.
     *
     * @return void
     */
    abstract public function handle();
}
