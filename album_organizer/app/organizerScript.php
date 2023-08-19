<?php

$binDirectory = __DIR__ . '/bin';
$logDirectory = __DIR__ . '/log';
$filesDirectory = __DIR__ . '/../files';

$seqNumberFilePath = "$logDirectory/seq.log";
$exifToolFilePath = "$binDirectory/exiftool";

require_once(__DIR__ . '/inc/OrganizerHelpers.php');

$helper = new OrganizerHelpers($exifToolFilePath);

$win32 = false;

if (PHP_OS === 'WINNT') {
    $exifToolFilePath = "$exifToolFilePath.exe";
    $win32 = true;
}

if (!file_exists($exifToolFilePath)) {
    echo PHP_EOL;
    echo "Error: Exiftool not found!!!";
    echo $win32 ? '' : PHP_EOL, PHP_EOL;
    exit;
}

if (!is_dir($filesDirectory)) {
    echo PHP_EOL;
    echo "Error: No files-directory!!!";
    echo $win32 ? '' : PHP_EOL, PHP_EOL;
    exit;
}

if (!is_dir($logDirectory)) {
    mkdir($logDirectory);
}

if (!file_exists($seqNumberFilePath)) {
    file_put_contents($seqNumberFilePath, 1);
}

$seq = (int) trim(file_get_contents($seqNumberFilePath));

if ($seq < 1) {
    file_put_contents($seqNumberFilePath, 1);
    $seq = 1;
}

chdir($filesDirectory);

echo PHP_EOL;
echo 'Changed to directory: ' . getcwd();
echo PHP_EOL;

sleep(1);

$files = $helper->getFilesInDirectory($filesDirectory);

if (count($files) === 0) {
    echo PHP_EOL;
    echo "No files to sort and rename!!!";
    echo $win32 ? '' : PHP_EOL, PHP_EOL;
    exit;
}

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

        if (!is_dir($directoryName)) {
            mkdir($directoryName);
        }

        $newFileName = strtolower($file);
        $newFilePath = "$directoryName/$newFileName";

        rename($file, $newFilePath);

        echo "Old file-path: ./$file" . PHP_EOL;
        echo "New file-path: ./$newFilePath" . PHP_EOL;

    } else {
        $directoryName = $helper->extractYear($dateTimeData);

        if (!is_dir($directoryName)) {
            mkdir($directoryName);
        }

        $randomHex = bin2hex(random_bytes(2));
        $paddedSeq = str_pad($seq, 8, 0, STR_PAD_LEFT);

        $newFileName = $dateTimeData . '_' . $randomHex . '_' . $paddedSeq . '.' . $fileExtension;
        $newFilePath = "$directoryName/$newFileName";

        file_put_contents($seqNumberFilePath, ++$seq);

        rename($file, $newFilePath);

        echo "Old file-path: ./$file" . PHP_EOL;
        echo "New file-path: ./$newFilePath" . PHP_EOL;

    }

    echo PHP_EOL;
}
