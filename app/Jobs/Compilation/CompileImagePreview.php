<?php

namespace App\Jobs\Compilation;

use App\Watermark;
use Illuminate\Database\Eloquent\Model;
use Intervention\Image\Constraint;
use Intervention\Image\Image;
use Intervention\Image\Facades\Image as ImageLibrary;
use Illuminate\Support\Facades\Storage;

class CompileImagePreview extends CompileFilePreview
{
    private const PREVIEW_WIDTH = 1500;
    private const PREVIEW_HEIGHT = 1500;

    /**
     * Create a new job instance.
     *
     * CompileImagePreview constructor.
     * @param Model $compiledModel
     * @param array $filters
     * @param string $source
     * @param string $destination
     * @param string $visibility
     */
    public function __construct(Model $compiledModel, array $filters, string $source, string $destination, bool $private, $beginEvent, $endEvent, $failedEvent)
    {
        parent::__construct($compiledModel, $filters, $source, $destination, $private, $beginEvent, $endEvent, $failedEvent);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function compileFile()
    {
        ini_set('memory_limit','256M');

        $image = ImageLibrary::make(Storage::get($this->source));
        $image->resize(self::PREVIEW_WIDTH, self::PREVIEW_HEIGHT, function (Constraint $constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $this->applyBlur($image);
        $this->applyResolution($image);
        $this->applyWatermark($image);

        $visibility = $this->private ? 'private' : 'public';
        Storage::put($this->destination, $image->stream(), $visibility);
        $image->destroy();
    }


    private function applyBlur(Image &$image)
    {
        if (isset($this->filters['blur'])) {
            $blurAmount = $this->filters['blur'] * (max($image->getWidth(), $image->getHeight()) / 250);
            $image->blur($blurAmount);
        }
    }

    private function applyResolution(Image &$image)
    {
        if (isset($this->filters['pixel_size'])) {
            $pixelSize = $this->filters['pixel_size'] * (max($image->getWidth(), $image->getHeight()) / 100);

            $image->resize($image->getWidth() / $pixelSize, $image->getHeight() / $pixelSize);
            $image->resize($image->getWidth() * $pixelSize, $image->getHeight() * $pixelSize);
        }
    }

    private function applyWatermark(Image &$image)
    {
        if (isset($this->filters['watermark'])) {
            $watermarkId = $this->filters['watermark'];
            $watermark = Watermark::find($watermarkId);

            $watermarkImage = ImageLibrary::make($watermark->getFile());

            switch($watermark->dimension){
                case 'same':
                    //Do nothing
                    break;
                case 'fill-width':
                    $watermarkImage->widen($image->getWidth());
                    break;
                case 'fill-height':
                    $watermarkImage->heighten($image->getHeight());
                    break;
                case 'fill-both':
                    $watermarkImage->fit($image->getWidth(), $image->getHeight());
                    break;
            }

            $image->insert($watermarkImage, $watermark->position);
        }
    }
}
