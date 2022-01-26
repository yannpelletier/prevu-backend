<?php

namespace App\Jobs\Thumbnails;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Constraint;
use Intervention\Image\Facades\Image;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Filesystem\Filesystem;

class CompileImageThumbnail extends CompileFileThumbnail
{
    private const THUMBNAIL_WIDTH = 400;
    private const THUMBNAIL_HEIGHT = 400;

    /**
     * Create a new job instance.
     **
     * @param string $source
     * @param string $destination
     * @param string $private
     */
    public function __construct(string $source, string $destination, bool $private)
    {
        parent::__construct($source, $destination, $private);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $image = Image::make(Storage::get($this->source));
        $image->resize(self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT, function (Constraint $constraint) {
            $constraint->aspectRatio();
        })->stream();

        $visibility = $this->private ? 'private' : 'public';
        Storage::put($this->destination, $image, $visibility);
        $image->destroy();
    }
}
