<?php

$binDirectory = __DIR__ . '/bin';
$logDirectory = __DIR__ . '/log';
$filesDirectory = __DIR__ . '/../files';

$exifToolFilePath = "$binDirectory/exiftool";
$seqNumberFilePath = "$logDirectory/seq.log";

if (!file_exists($exifToolFilePath)) {
  echo "\n";
  echo "Error: Exiftool not found!!!\n";
  echo "\n";
  exit;
}

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

echo "\n";
echo "Change directory to: $filesDirectory\n";

sleep(1);

chdir($filesDirectory);

$files = preg_grep('/^([^.])/', scandir($filesDirectory));
$files = array_filter($files, "filterDirectories");

if (count($files) === 0) {
  echo "\n";
  echo "No files to sort and rename!!!\n";
  echo "\n";
  exit;
}

foreach ($files as $file) {

  $fileError = false;
  $dateTimeData = '';
  $fileExtension = '';

  $mimeType = mime_content_type($file);

  // echo "Now processing: $file\n";
  // echo "MimeType: $mimeType\n";

  switch ($mimeType) {
    case 'image/jpeg':
      $fileExtension = 'jpg';
      $tags = ['DateTimeOriginal', 'CreateDate', 'ModifyDate', 'FileCreationDateTime'];
      $dateTimeData = extractFileDateTimeTag($tags, $file);
      break;
    case 'image/png':
      $fileExtension = 'png';
      $tags = ['FileModifyDate'];
      $dateTimeData = extractFileDateTimeTag($tags, $file);
      break;
    case 'video/quicktime':
      $fileExtension = 'mov';
      $tags = ['MediaCreateDate'];
      $dateTimeData = extractFileDateTimeTag($tags, $file);
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

    echo "\n";
    echo "Old file-path: ./$file\n";
    echo "New file-path: ./$newFilePath\n";

  } else {
    $directoryName = extractFileYear($dateTimeData);

    if (!is_dir($directoryName)) {
      mkdir($directoryName);
    }

    $randomHex = bin2hex(random_bytes(2));
    $paddedSeq = str_pad($seq, 8, 0, STR_PAD_LEFT); // 00000001

    $newFileName = $dateTimeData . '_' . $randomHex . '_' . $paddedSeq . '.' . $fileExtension;
    $newFilePath = "$directoryName/$newFileName";

    rename($file, $newFilePath);

    file_put_contents($seqNumberFilePath, ++$seq);

    echo "\n";
    echo "Old file-path: ./$file\n";
    echo "New file-path: ./$newFilePath\n";

  }
}

echo "\n";
