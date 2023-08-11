<?php

function getFileDateTimeData(string $parameter, string $file)
{
    global $exifToolFilePath;
    $exifToolCommand = sprintf("%s -T -%s -d %s %s", $exifToolFilePath, $parameter, '"%Y%m%d%H%M%S"', $file);
    return trim(shell_exec($exifToolCommand));
}

function extractFileDateTimeTag(array $tags, string $file)
{
    foreach ($tags as $tag) {
        if (strlen(getFileDateTimeData($tag, $file)) === 14) {
            return getFileDateTimeData($tag, $file);
        }
    }
}

function extractFileYear(string $dateTimeData)
{
    return substr($dateTimeData, 0, 4);
}

function filterDirectories($file)
{
    return mime_content_type($file) !== 'directory';
}
