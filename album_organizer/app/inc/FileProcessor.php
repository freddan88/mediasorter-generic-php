<?php

class FileProcessor
{
    public function file(Helper $helper, bool $fileError, null|string $dateTimeData, int $seqNumber, string $fileExtension, $file)
    {
        echo PHP_EOL;

        if ($fileError || is_null($dateTimeData)) {
            $directoryName = 'unsupported';
            $helper->createDirectoryIfMissing($directoryName);

            $newFilePath = $helper->getNewFilePath($directoryName, strtolower($file));
            rename($file, $newFilePath);

            $helper->printMessage("Old file-path: ./$file");
            $helper->printMessage("New file-path: ./$newFilePath");
        } else {
            $directoryName = $helper->extractYear($dateTimeData);
            $helper->createDirectoryIfMissing($directoryName);

            $paddedSeqNumber = str_pad($seqNumber, 8, 0, STR_PAD_LEFT);
            $helper->updateSequenceNumber($seqNumber);
            $randomHex = bin2hex(random_bytes(2));

            // $newFileName = $dateTimeData . '_' . $randomHex . '_' . $paddedSeq . '.' . $fileExtension;
            $newFileName = $dateTimeData . '_' . $paddedSeqNumber . '.' . $fileExtension;

            $newFilePath = $helper->getNewFilePath($directoryName, $newFileName);
            rename($file, $helper->getNewFilePath($directoryName, $newFilePath));

            $helper->printMessage("Old file-path: ./$file");
            $helper->printMessage("New file-path: ./$newFilePath");
        }
    }
}
