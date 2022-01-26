<?php

namespace App;

use App\Jobs\DeleteFile;
use App\Traits\HasFiles;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFiles;

    protected $fillable = ['file'];

    protected $casts = [
        "id" => "integer",
        "user_id" => "integer",
    ];

    protected $directories = [
        'user_assets' => 'main',
    ];

    public function getFileUrl(){
        return Storage::url($this->getMainStoragePath());
    }

    public function setFileAttribute(UploadedFile $file){
        $this->uploadMainFile($file);
    }
}
