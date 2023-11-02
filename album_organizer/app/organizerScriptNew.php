<?php

$binDirectory = __DIR__ . '/bin';
$logDirectory = __DIR__ . '/log';
$filesDirectory = __DIR__ . '/../files';

require_once(__DIR__ . '/inc/Validator.php');
require_once(__DIR__ . '/inc/Helper.php');

$exifToolFilePath = "$binDirectory/exiftool";

if (PHP_OS === 'WINNT') {
    $exifToolFilePath = "$exifToolFilePath.exe";
}

$helper = new Helper($exifToolFilePath, $logDirectory);
$validator = new Validator();

$validator->exifToolExists($exifToolFilePath);
$validator->directoryExists($filesDirectory);
$seqNumber = $helper->initializeSequenceNumber();
$validator->directoryHasFiles($filesDirectory);

echo PHP_EOL;

foreach ($files as $file) {

    $fileError = false;
    $dateTimeData = '';
    $fileExtension = '';

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

    if ($fileError) {
        $directoryName = 'unsupported';
        $helper->createDirectoryIfMissing($directoryName);

        $newFileName = strtolower($file);
        rename($file, $helper->getNewFilePath($directoryName, $newFileName));

        $helper->printMessage("Old file-path: ./$file");
        $helper->printMessage("New file-path: ./$newFilePath");
    } else {
        $directoryName = $helper->extractYear($dateTimeData);
        $helper->createDirectoryIfMissing($directoryName);

        $randomHex = bin2hex(random_bytes(2));
        $paddedSeqNumber = str_pad($seqNumber, 8, 0, STR_PAD_LEFT);
        $helper->updateSequenceNumber($seqNumber);

        // $newFileName = $dateTimeData . '_' . $randomHex . '_' . $paddedSeq . '.' . $fileExtension;
        $newFileName = $dateTimeData . '_' . $paddedSeqNumber . '.' . $fileExtension;
        rename($file, $helper->getNewFilePath($directoryName, $newFileName));

        $helper->printMessage("Old file-path: ./$file");
        $helper->printMessage("New file-path: ./$newFilePath");
    }

    echo PHP_EOL;
}
