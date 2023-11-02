<?php

$filesDirectory = __DIR__ . '/../files';
$exifToolPath = '/bin/exiftool';

require_once(__DIR__ . '/inc/FileProcessor.php');
require_once(__DIR__ . '/inc/Validator.php');
require_once(__DIR__ . '/inc/Helper.php');

$validator = new Validator();
$process = new FileProcessor();
$helper = new Helper($exifToolPath);
$validator->exifToolExists($exifToolPath);
$validator->directoryExists($filesDirectory);
$seqNumber = $helper->initializeSequenceNumber();
$files = $validator->directoryHasFiles($filesDirectory);

foreach ($files as $file) {
    $fileError = false;
    $fileExtension = '';
    $dateTimeData = null;
    $mimeType = mime_content_type($file);

    switch ($mimeType) {
        case 'image/jpeg':
            $fileExtension = 'jpg';
            $tags = ['DateTimeOriginal', 'CreateDate', 'ModifyDate', 'FileCreationDateTime'];
            $dateTimeData = $helper->extractFileDateTimeTag($tags, $file);
            break;
        case 'image/png':
            $fileExtension = 'png';
            $tags = ['FileModifyDate'];
            $dateTimeData = $helper->extractFileDateTimeTag($tags, $file);
            break;
        case 'video/quicktime':
            $fileExtension = 'mov';
            $tags = ['MediaCreateDate'];
            $dateTimeData = $helper->extractFileDateTimeTag($tags, $file);
            break;
        default:
            $fileError = true;
            break;
    }

    $process->file($helper, $fileError, $dateTimeData, $seqNumber, $fileExtension, $file);
}
