<?php

class Helper
{
    private $exifToolFilePath = '';
    private $logDirectory = '';

    function __construct(string $exifToolFilePath)
    {
        $this->exifToolFilePath = $exifToolFilePath;
        $this->logDirectory = __DIR__ . '/../log';
    }

    private function getFileDateTimeData(string $tagName, $file)
    {
        $exifToolCommand = sprintf("%s -T -%s -d %s %s", $this->exifToolFilePath, $tagName, '"%Y%m%d%H%M%S"', $file);
        return trim(shell_exec($exifToolCommand));
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
            file_put_contents($seqNumberFilePath, 0);
        }

        $seqNumber = (int) trim(file_get_contents($seqNumberFilePath));

        if ($seqNumber < 0) {
            file_put_contents($seqNumberFilePath, 0);
            $seqNumber = 0;
        }

        return $seqNumber;
    }

    public function updateSequenceNumber(int $seqNumber): void
    {
        $seqNumberFilePath = "$this->logDirectory/seq.log";
        file_put_contents($seqNumberFilePath, $seqNumber);
    }

    public function getNewFilePath(string $basePath, string $fileName): string
    {
        return "$basePath/$fileName";
    }

    public function extractFileDateTimeTag(array $tags, $file)
    {
        foreach ($tags as $tag) {
            $fileDateTimeData = $this->getFileDateTimeData($tag, $file);
            if (strlen($fileDateTimeData) === 14) {
                return $fileDateTimeData;
            }
        }
        return null;
    }

    public function extractYear(string $dateTimeData)
    {
        return substr($dateTimeData, 0, 4);
    }
}
