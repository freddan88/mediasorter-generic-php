<?php

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
            echo "Error: Exiftool not found!!!";
            echo $this->win32 ? '' : PHP_EOL, PHP_EOL;
            exit;
        }
    }

    public function directoryExists(string $filesDirectory): void
    {
        if (!is_dir($filesDirectory)) {
            echo PHP_EOL;
            echo "Error: No files-directory!!!";
            echo $this->win32 ? '' : PHP_EOL, PHP_EOL;
            exit;
        }
    }

    public function directoryHasFiles(string $filesDirectory): void
    {
        function filterDirectories($file)
        {
            return filetype($file) !== 'dir';
        }

        chdir($filesDirectory);

        echo PHP_EOL;
        echo 'Changed to directory: ' . getcwd();
        echo PHP_EOL;

        sleep(1);

        $files = preg_grep('/^([^.])/', scandir($filesDirectory));
        $files = array_filter($files, "filterDirectories");

        if (count($files) === 0) {
            echo PHP_EOL;
            echo "No files to sort and rename!!!";
            echo $this->win32 ? '' : PHP_EOL, PHP_EOL;
            exit;
        }
    }
}
