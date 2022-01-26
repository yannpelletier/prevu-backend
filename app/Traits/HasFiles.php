<?php

namespace App\Traits;

use App\Jobs\Compilation\CompileImagePreview;
use App\Jobs\DeleteFile;
use App\Jobs\Thumbnails\CompileImageThumbnail;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image as ImageLibrary;
use UnexpectedValueException;

trait HasFiles
{
    use DispatchesJobs;

    private static $FILE_ID_LENGTH = 20; // keep it low for local storage compatibility
    private static $THUMBNAILS_EXTENSION = 'png';

    public function getFileTypesInfoAttribute()
    {
        return [
            'image' => [
                'thumbnail_job' => CompileImageThumbnail::class,
                'compile_job' => CompileImagePreview::class,
                'filters' => ['blur', 'pixel_size', 'watermark'],
                'metadata' => function (UploadedFile $file) {
                    $image = ImageLibrary::make($file);
                    return [
                        'dimensions' => [
                            'text' => sprintf("%d X %d", $image->getWidth(), $image->getHeight()),
                            'icon' => 'mdi-image-size-select-actual',
                        ]
                    ];
                }
            ],
        ];
    }

    public static $extensionTypeMap = [
        'png' => 'image',
        'jpg' => 'image',
        'jpeg' => 'image',
        'gif' => 'image',
    ];

    public static $filtersInfo = [
        'blur' => [
            'type' => 'number',
            'min' => 0,
            'max' => 10,
            'step' => 1,
            'default' => 0,
            'name' => 'Blur',
            'unit' => '%',
            "note" => 'The intensity of this filter is calculated relative to the biggest dimension of the image'
        ],
        'pixel_size' => [
            'type' => 'number',
            'min' => 0,
            'max' => 2,
            'step' => 0.20,
            'default' => 0,
            'name' => 'Pixel Size',
            'unit' => '%',
            "note" => 'The intensity of this filter is calculated relative to the biggest dimension of the image'
        ],
        'watermark' => [
            'type' => 'watermark',
            'default' => null,
            'name' => 'Applied Watermark',
        ],
    ];

    private $mainDirectory = null;
    private $compiledDirectory = null;

    private $hasPublicDirectory = false;
    private $hasPrivateDirectory = false;

    /**
     * Defines the model's directories that are used to store files and their role
     *
     * Accepted directory roles:
     * main: folder used to store the main files that are used to be modified *required*
     * compiled: folder used to store compiled versions of the main file      *optional*
     * thumbnail: folder used to store thumbnails of the main file            *optional*
     *
     * example: ['originals' => 'main', 'previews' => 'compiled' ]
     *
     * Can also define the visibility of the file with the "private" property (default false)
     *
     * example: ['originals' => ['type' => 'main', 'private' => true]]
     */

    /**
     * This method is called upon instantiation of the Eloquent Model that the trait is used on
     *
     * @return void
     */
    public function initializeHasFiles()
    {
        $this->parseDirectories();
        $this->validateDirectories();
        if (isset($this->compiledDirectory)) {
            $this->casts['filters'] = 'array';
            $this->fillable[] = 'filters';
            $this->attributes['filters'] = "";

            $this->fillable[] = 'compilation_state';
            $this->attributes['compilation_state'] = "draft";
        }

        $this->fillable[] = 'extension';
        $this->attributes['extension'] = "";
        if ($this->hasPublicDirectory) {
            $this->fillable[] = 'public_file_id';
            $this->attributes['public_file_id'] = "";
        }
        if ($this->hasPrivateDirectory) {
            $this->fillable[] = 'private_file_id';
            $this->attributes['private_file_id'] = "";
        }
    }

    private function parseDirectories()
    {
        foreach ($this->directories as $directoryName => $directoryInfo) {
            if (is_array($directoryInfo)) {
                $this->directories[$directoryName]['private'] = isset($directoryInfo['private']) ? $directoryInfo['private'] : false;
            } else if (is_string($directoryInfo)) {
                $newDirectoryInfo = [];
                $newDirectoryInfo['private'] = false;
                $newDirectoryInfo['type'] = $directoryInfo;

                $this->directories[$directoryName] = $newDirectoryInfo;
            }

            $directoryInfo = $this->directories[$directoryName];
            if ($directoryInfo['type'] === 'main') {
                $this->mainDirectory = $directoryName;
            } else if ($directoryInfo['type'] === 'compiled') {
                $this->compiledDirectory = $directoryName;
            }

            if ($directoryInfo['private']) {
                $this->hasPrivateDirectory = true;
            } else {
                $this->hasPublicDirectory = true;
            }
        }
    }

    private function validateDirectories()
    {
        if (!isset($this->directories)) {
            throw new UnexpectedValueException('Trait HasFiles except "directories" array property to define where to store its files');
        } else {
            $mainDirectoryCount = 0;
            $compiledDirectoryCount = 0;
            foreach ($this->directories as $directoryName => $directoryInfo) {
                if (!isset($directoryInfo['type'])) {
                    throw new UnexpectedValueException("Invalid type given to $directoryName directory name property: string expected");
                } else if (!is_bool($directoryInfo['private'])) {
                    throw new UnexpectedValueException("Invalid type given to $directoryName directory 'private' property: boolean expected");
                }

                $directoryType = $directoryInfo['type'];
                if ($directoryType === "main") {
                    $mainDirectoryCount += 1;
                } else if ($directoryType === "compiled") {
                    $compiledDirectoryCount += 1;
                } else if ($directoryType !== "thumbnail") {
                    throw new UnexpectedValueException('Directory types must be one of the following: main, thumbnail, compiled');
                }
            }

            if ($mainDirectoryCount !== 1) {
                throw new UnexpectedValueException('Directories must contain exactly one directory of type "main"');
            } else if ($compiledDirectoryCount > 1) {
                throw new UnexpectedValueException('Directories can\'t contain more than one "compiled" directory');
            }
        }
    }

    /*
     * MAIN FILE FUNCTIONS
     */
    public function uploadMainFile(UploadedFile $file)
    {
        if ($this->hasPublicDirectory) {
            $this->attributes['public_file_id'] = Str::random(self::$FILE_ID_LENGTH);
        }
        if ($this->hasPrivateDirectory) {
            $this->attributes['private_file_id'] = Str::random(self::$FILE_ID_LENGTH);
        }
        $this->attributes['extension'] = Str::lower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));

        Storage::putFileAs($this->mainDirectory, $file, $this->getMainFileName(), 'public');
    }

    public function getMainStoragePath()
    {
        $mainFileName = $this->getMainFileName();
        return sprintf('%s/%s', $this->mainDirectory, $mainFileName);
    }

    public function getMainFileName()
    {
        $usedFileId = null;
        if ($this->isMainDirectoryPrivate()) {
            $usedFileId = $this->attributes['private_file_id'];
        } else {
            $usedFileId = $this->attributes['public_file_id'];
        }
        return sprintf('%s.%s', $usedFileId, $this->attributes['extension']);
    }

    public function getMainFileMetaData(UploadedFile $file)
    {
        //$fileStoragePath = $this->getMainStoragePath();
        $fileInfo = $this->getFileTypeInfo();
        return $fileInfo['metadata']($file);
    }

    public function isMainDirectoryPrivate()
    {
        return $this->directories[$this->mainDirectory]['private'];
    }

    public function hasMainFile()
    {
        return Storage::exists($this->getMainStoragePath());
    }

    public function deleteMainFile()
    {
        DeleteFile::dispatchNow($this->getMainStoragePath());
    }

    /*
     * THUMBNAIL FILE FUNCTIONS
     */
    public function createMainFileThumbnail($directoryName)
    {
        $fileTypeInfo = $this->getFileTypeInfo();
        $fileTypeInfo['thumbnail_job']::dispatchNow(
            $this->getMainStoragePath(),
            $this->getDirectoryStoragePath($directoryName),
            false
        );
    }

    public function createDirectoryFileThumbnail($sourceDirectoryName, $destinationDirectoryName)
    {
        $fileTypeInfo = $this->getFileTypeInfo();
        $fileTypeInfo['thumbnail_job']::dispatchNow(
            $this->getDirectoryStoragePath($sourceDirectoryName),
            $this->getDirectoryStoragePath($destinationDirectoryName),
            false
        );
    }

    /*
     * COMPILED FILE FUNCTIONS
     */
    public function createMainFileCompilation($directoryName)
    {
        $usedFilters = $this->getUsedFilters();
        $beginEvent = method_exists($this, 'getCompilationBeginEvent') ? $this->getCompilationBeginEvent() : null;
        $endEvent = method_exists($this, 'getCompilationEndEvent') ? $this->getCompilationEndEvent() : null;
        $failedEvent = method_exists($this, 'getCompilationFailedEvent') ? $this->getCompilationFailedEvent() : null;

        $fileTypeInfo = $this->getFileTypeInfo();
        return $this->dispatch(new $fileTypeInfo['compile_job'](
            $this,
            $usedFilters,
            $this->getMainStoragePath(),
            $this->getDirectoryStoragePath($directoryName),
            false,
            $beginEvent,
            $endEvent,
            $failedEvent
        ));
    }

    /*
     * GENERIC DIRECTORY FUNCTIONS
     */
    public function getDirectoryFileName($directoryName)
    {
        $usedFileId = null;
        if ($this->isDirectoryPrivate($directoryName)) {
            $usedFileId = $this->attributes['private_file_id'];
        } else {
            $usedFileId = $this->attributes['public_file_id'];
        }

        if ($this->getDirectoryType($directoryName) === 'thumbnail') {
            return sprintf('%s.%s', $usedFileId, self::$THUMBNAILS_EXTENSION);
        } else {
            return sprintf('%s.%s', $usedFileId, $this->attributes['extension']);
        }
    }

    public function getDirectoryStoragePath($directoryName)
    {
        $directoryFileName = $this->getDirectoryFileName($directoryName);
        return sprintf('%s/%s', $directoryName, $directoryFileName);
    }

    public function getDirectoryType($directoryName)
    {
        return $this->directories[$directoryName]['type'];
    }

    public function isDirectoryPrivate($directoryName)
    {
        return $this->directories[$directoryName]['private'];
    }

    public function hasDirectoryFile($directoryName)
    {
        return Storage::exists($this->getDirectoryStoragePath($directoryName));
    }

    public function deleteDirectoryFile($directoryName)
    {
        DeleteFile::dispatchNow($this->getDirectoryStoragePath($directoryName));
    }

    public function deleteCompilationJob()
    {
        $id = $this->attributes['compilation_job_id'];
        if ($id) {
            DB::table('jobs')->whereId($id)->delete();
        }
    }

    protected function getUsedFilters()
    {
        $usedFilters = [];
        foreach ($this->filtersInfo as $filterId => $filterInfo) {
            if ($this->filters[$filterId] !== $filterInfo['default']) {
                $usedFilters[$filterId] = $this->filters[$filterId];
            }
        }
        return $usedFilters;
    }

    public function setFiltersAttribute(array $inputFilters)
    {
        $newFilters = [];
        foreach ($this->filtersInfo as $filterId => $filterInfo) {
            if (array_key_exists($filterId, $inputFilters)) {
                $newFilters[$filterId] = $inputFilters[$filterId];
            } else {
                $newFilters[$filterId] = $filterInfo['default'];
            }
        }

        $newFiltersEnd = json_encode($newFilters);
        if ($newFiltersEnd !== $this->attributes['filters']) {
            $this->attributes['filters'] = $newFiltersEnd;

            $usedFilters = $this->getUsedFilters();
            if (count($usedFilters) > 0) {
                $this->deleteCompilationJob();
                $this->attributes['compilation_state'] = 'queued';
                $this->attributes['compilation_job_id'] = $this->createMainFileCompilation($this->compiledDirectory);
            }
        }
    }

    public function getFilterRules(string $field)
    {
        $rules = [];
        $rules[$field] = ['array'];
        $rules[$field . '.*'] = [Rule::in(array_keys($this->filtersInfo))];

        foreach ($this->filtersInfo as $filterId => $filterInfo) {
            $ruleField = sprintf('%s.%s', $field, $filterId);
            switch ($filterInfo['type']) {
                case 'number':
                    $rules[$ruleField] = ['numeric', 'min:' . $filterInfo['min'], 'max:' . $filterInfo['max']];
                    break;
                case 'watermark':
                    $rules[$ruleField] = ['nullable', 'exists:watermarks,id'];
                    break;
                default:
                    break;
            }
        }
        return $rules;
    }

    static public function getMimesRule()
    {
        $mimesRule = "mimes:";
        foreach (array_keys(self::$extensionTypeMap) as $extension) {
            $mimesRule .= $extension . ',';
        }
        return rtrim($mimesRule, ',');
    }

    public function getFiltersInfoAttribute()
    {
        $fileTypeInfo = $this->getFileTypeInfo();

        $filtersInfo = [];
        foreach ($fileTypeInfo['filters'] as $filter) {
            $filtersInfo[$filter] = self::$filtersInfo[$filter];
        }
        return $filtersInfo;
    }

    private function getFileTypeInfo()
    {
        $extensionType = self::$extensionTypeMap[$this->attributes['extension']];
        return $this->fileTypesInfo[$extensionType];
    }
}
