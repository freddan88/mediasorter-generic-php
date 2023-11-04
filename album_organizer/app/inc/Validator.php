<?php

declare(strict_types=1);

class Validator
{
    private $win32 = false;

    function __construct()
    {
        $this->win32 = PHP_OS === 'WINNT';
    }

    public function exifToolExists(string $exifToolFilePath): void
    {
        if (!file_exists($exifToolFilePath)) {
            echo PHP_EOL;
            echo "Error: Exiftool not found";
            echo $this->win32 ? '' : PHP_EOL, PHP_EOL;
            exit;
        }
    }

    public function configFile(){
        $configPath = __DIR__ . '/../../config.ini';
        if (!file_exists($configPath)) {
            echo PHP_EOL;
            echo 'Error: Configuration-file missing' . PHP_EOL;
            echo "Please create the file: 'config.ini' in the directory: album_organizer";
            echo $this->win32 ? '' : PHP_EOL, PHP_EOL;
            exit;
        }

        return (object) parse_ini_file($configPath, true);
    }

    public function directoryExists(string $filesDirectory): void
    {
        if (!is_dir($filesDirectory)) {
            echo PHP_EOL;
            echo "Error: No files-directory";
            echo $this->win32 ? '' : PHP_EOL, PHP_EOL;
            exit;
        }
    }

    public function directoryHasFiles(string $filesDirectory): array
    {
        function filterDirectories($file)
        {
            return filetype($file) !== 'dir';
        }

        chdir($filesDirectory);

        echo $this->win32 ? PHP_EOL : '';
        echo 'Changed to directory: ' . getcwd();
        echo PHP_EOL;

        sleep(1);

        $files = preg_grep('/^([^.])/', scandir($filesDirectory));
        $files = array_filter($files, "filterDirectories");

        if (!is_array($files) || count($files) === 0) {
            echo PHP_EOL;
            echo "No files to sort and rename";
            echo $this->win32 ? '' : PHP_EOL, PHP_EOL;
            exit;
        }

        return $files;
    }
}
