<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

/**
 * Generates a zip file containing both the frontend and backend of the application.
 * This zip file can then be deployed to AWS Beanstalk.
 * Made to work only with windows paths.
 *
 * Class Build
 * @package App\Console\Commands
 */
class Build extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'build
                            {out=PrevU_build : The name of the output file.}
                            {--backend=PrevU_backend : The backend directory}
                            {--frontend=PrevU_frontend : The frontend directory}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "
    Generates a zip file containing both the frontend and backend of the application.
    This zip file can then be deployed to AWS Beanstalk.
    ";

    private const IGNORED_DIRECTORIES = ['.idea', '.git', 'vendor'];
    private const IGNORED_FILES = ['.env'];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Main function.
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("================================");
        $this->info("=== PrevU Supremacy Spreader ===");
        $this->info("================================");
        $this->info("DON'T forget to build the Vue.Js application beforehand!");
        $workingDirectory = realpath(base_path() . '\\..') . '\\';
        $frontendDirectory = $workingDirectory . $this->option('frontend');
        $backendDirectory = $workingDirectory . $this->option('backend');
        $outDirectory = $workingDirectory . $this->argument('out');
        $zipPath = $workingDirectory . $this->argument('out') . '.zip';

        if (File::exists($outDirectory)) {
            $this->error("Output directory $outDirectory already exists!");
            return;
        }
        if (File::exists($zipPath)) {
            $this->error("Zip archive $zipPath already exists!");
            return;
        }

        $this->info("Copying folder $backendDirectory to $outDirectory");
        $this->copyDirectory($backendDirectory, $outDirectory);

        $this->info("Copying $frontendDirectory\\dist to $outDirectory\\public");
        File::copyDirectory("$frontendDirectory\\dist", "$outDirectory\\public");
        File::delete("$outDirectory\\public\\index.html");

        /*
        $this->info("Building Vue project");
        exec("$frontendDirectory\\node_modules\\.bin\\vue-cli-service build ..\\..\\main.js");
        */

        $frontendIndexFilePath = "$frontendDirectory\\dist\\index.html";
        $buildIndexFilePath = "$outDirectory\\resources\\views\\vue.blade.php";
        $this->info("Copying $frontendIndexFilePath to $buildIndexFilePath");
        File::copy($frontendIndexFilePath, $buildIndexFilePath);

        $this->info("Renaming .env.prod to .env");
        File::move("$outDirectory\\.env.prod", "$outDirectory\\.env");

        $this->info("Initializing a dummy git repo in $outDirectory");
        exec("cd \"$outDirectory\" && git init");

        $this->info("Adding all files for the commit");
        exec("cd \"$outDirectory\" && git add .");

        $this->info("Committing the repo");
        exec("cd \"$outDirectory\" && git commit -m initial");

        $this->info("Creating zip archive $zipPath");
        // $this->zipDirectory($outDirectory, $zipPath);
        exec("cd \"$outDirectory\" && git archive -o \"$zipPath\" HEAD");

        $this->info("We're done! Let PrevU dominate the world!!!");
    }

    private function copyDirectory($source, $destination)
    {
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source));

        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $absoluteSourceDir = File::dirname($filePath);
                $relativeDir = substr($absoluteSourceDir, strlen($source) + 1);
                $absoluteDestinationDir = "$destination\\$relativeDir";
                // extracting filename with substr/strlen
                $relativePath = substr($filePath, strlen($source) + 1);
                $ignored = false;
                foreach (self::IGNORED_DIRECTORIES as $ignoredDirectory) {
                    if (Str::startsWith($relativePath, "$ignoredDirectory\\")) {
                        $ignored = true;
                        break;
                    }
                }
                foreach (self::IGNORED_FILES as $ignoredFile) {
                    if ($relativePath === $ignoredFile) {
                        $ignored = true;
                        break;
                    }
                }
                if (!$ignored) {
                    if (!File::exists($absoluteDestinationDir)) {
                        File::makeDirectory($absoluteDestinationDir, 0755, true);
                    }
                    File::copy($filePath, "$destination\\$relativePath");
                }
            }
        }
    }

    private function zipDirectory($directory, $zipPath)
    {
        // TODO: Ignore .git directory
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
        foreach ($files as $name => $file) {
            // We're skipping all subfolders
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();

                // extracting filename with substr/strlen
                $relativePath = substr($filePath, strlen($directory) + 1);

                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();
    }
}
