<?php

declare(strict_types=1);

require_once(__DIR__ . '/inc/Helper.php');
require_once(__DIR__ . '/inc/Validator.php');
require_once(__DIR__ . '/inc/FileProcessor.php');

$validator = new Validator();
$config = $validator->configFile();

$processor = new FileProcessor();
$helper = new Helper($config->exif_tool_path);
$validator->exifToolExists($config->exif_tool_path);
$validator->directoryExists($config->files_directory);
$files = $validator->directoryHasFiles($config->files_directory);
$seqNumber = $helper->initializeSequenceNumber();

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
        case 'video/mpeg':
            $tags = ['FileModifyDate'];
            $processor->setFileOptions('mpg', $helper->extractFileDateTimeTag($tags, $file));
            break;
        default:
            $processor->setFileError();
            break;
    }

    $processor->processFile($helper, ++$seqNumber, $mimeType, $file);
}

$helper->printMessage('');
