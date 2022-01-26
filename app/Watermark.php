<?php

namespace App;

use App\Traits\HasEnums;
use App\Traits\HasFiles;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Watermark extends Model
{
    use HasFiles, HasEnums;

    protected $fillable = ['file', 'name', 'position', 'dimension'];

    /**
     * The "position" attribute enum
     *
     * @var array
     */
    protected $enumPositions = [
        'top-left',
        'bottom-left',
        'top-right',
        'bottom-right',
        'center',
    ];

    /**
     * The "dimension" attribute enum
     *
     * @var array
     */
    protected $enumDimensions = [
        'same',
        'fill-width',
        'fill-height',
        'fill-both',
    ];

    /**
     * Default values for attributes
     * @var  array an array with attribute as key and default as value
     */
    protected $attributes = [
        'position' => 'center',
        'dimension' => 'same',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
    ];

    protected $directories = [
        'watermarks' => 'main',
    ];

    public function getFile()
    {
        if ($this->user_id !== null) {
            return Storage::get($this->getMainStoragePath());
        } else {
            $path = sprintf("%s.%s", $this->public_file_id, $this->extension);
            return Storage::disk('public')->get($path);
        }
    }

    public function getFileUrl()
    {
        if ($this->user_id !== null) {
            return Storage::url($this->getMainStoragePath());
        } else {
            $path = sprintf("%s.%s", $this->public_file_id, $this->extension);
            return Storage::disk('public')->url($path);
        }
    }

    public function setFileAttribute(UploadedFile $file)
    {
        $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $this->attributes['name'] = namify($fileName);
        $this->uploadMainFile($file);
    }
}
