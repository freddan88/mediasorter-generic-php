<?php

$filesDirectory = __DIR__ . '/../files';
$exifToolPath = __DIR__ . '/bin/exiftool.exe';

require_once(__DIR__ . '/inc/FileProcessor.php');
require_once(__DIR__ . '/inc/Validator.php');
require_once(__DIR__ . '/inc/Helper.php');

$validator = new Validator();
$processor = new FileProcessor();
$helper = new Helper($exifToolPath);
$validator->exifToolExists($exifToolPath);
$validator->directoryExists($filesDirectory);
$seqNumber = $helper->initializeSequenceNumber();
$files = $validator->directoryHasFiles($filesDirectory);

foreach ($files as $file) {
    $mimeType = mime_content_type($file);

    switch ($mimeType) {
        case 'image/jpeg':
            $tags = ['DateTimeOriginal', 'CreateDate', 'ModifyDate', 'FileCreationDateTime'];
            $processor->setFileOptions('jpg', $helper->extractFileDateTimeTag($tags, $file));
            break;
        case 'image/png':
            $tags = ['FileModifyDate'];
            $processor->setFileOptions('png', $helper->extractFileDateTimeTag($tags, $file));
            break;
        case 'video/quicktime':
            $tags = ['MediaCreateDate'];
            $processor->setFileOptions('mov', $helper->extractFileDateTimeTag($tags, $file));
            break;
        default:
            $processor->setFileError();
            break;
    }

    $processor->processFile($helper, ++$seqNumber, $mimeType, $file);
}
