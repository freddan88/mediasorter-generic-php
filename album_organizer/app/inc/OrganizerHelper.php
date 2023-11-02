<?php

final class OrganizerHelper
{
    private $exifToolFilePath = '';

    function __construct(string $exifToolFilePath)
    {
        $this->exifToolFilePath = $exifToolFilePath;
    }

    private function getFileDateTimeData(string $parameter, string $file)
    {
        $exifToolCommand = sprintf("%s -T -%s -d %s %s", $this->exifToolFilePath, $parameter, '"%Y%m%d%H%M%S"', $file);
        return trim(shell_exec($exifToolCommand));
    }

    public function getFilesInDirectory(string $filesDirectory): array
    {
        function filterDirectories($file)
        {
            return filetype($file) !== 'dir';
        }
        $files = preg_grep('/^([^.])/', scandir($filesDirectory));
        return array_filter($files, "filterDirectories");
    }

    public function extractFileDateTimeTag(array $tags, string $file)
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
