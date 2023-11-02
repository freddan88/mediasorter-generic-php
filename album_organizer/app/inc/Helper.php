<?php

class Helper
{
    private $exifToolFilePath = '';
    private $logDirectory = '';

    function __construct(string $exifToolFilePath, string $logDirectory)
    {
        $this->exifToolFilePath = $exifToolFilePath;
        $this->logDirectory = $logDirectory;
    }

    public function printMessage(string $message): void
    {
        echo $message . PHP_EOL;
    }

    public function createDirectoryIfMissing(string $directoryName)
    {
        if (!is_dir($directoryName)) {
            mkdir($directoryName);
        }
    }

    public function initializeSequenceNumber(): int
    {
        if (!is_dir($this->logDirectory)) {
            mkdir($this->logDirectory);
        }

        $seqNumberFilePath = "$this->logDirectory/seq.log";

        if (!file_exists($seqNumberFilePath)) {
            file_put_contents($seqNumberFilePath, 1);
        }

        $seqNumber = (int) trim(file_get_contents($seqNumberFilePath));

        if ($seqNumber < 1) {
            file_put_contents($seqNumberFilePath, 1);
            $seqNumber = 1;
        }

        return $seqNumber;
    }

    public function updateSequenceNumber(int $seqNumber): void
    {
        $seqNumberFilePath = "$this->logDirectory/seq.log";
        file_put_contents($seqNumberFilePath, ++$seqNumber);
    }

    public function getNewFilePath(string $basePath, string $fileName): string
    {
        return "$basePath/$fileName";
    }
}
