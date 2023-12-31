<?php

declare(strict_types=1);

class FileProcessor
{
    private $dateTimeData = null;
    private $fileExtension = '';
    private $fileError = false;

    private function printFileInfo(Helper $helper, string $mimeType, string $newFilePath, $file)
    {
        echo PHP_EOL;

        $helper->printMessage("------------------------------------");
        if ($this->fileExtension) {
            $helper->printMessage("File extension: $this->fileExtension");
        }
        $helper->printMessage("File mime-type: $mimeType");

        echo PHP_EOL;

        $helper->printMessage("Old file-path: ./$file");
        $helper->printMessage("New file-path: ./$newFilePath");
        $helper->printMessage("------------------------------------");
    }

    private function resetFileOptions()
    {
        $this->dateTimeData = null;
        $this->fileExtension = '';
        $this->fileError = false;
    }

    public function setFileError()
    {
        $this->fileError = true;
    }

    public function setFileOptions(string $fileExtension, null|string $dateTimeData)
    {
        $this->fileExtension = $fileExtension;
        $this->dateTimeData = $dateTimeData;
    }
    public function processFile(Helper $helper, object $config, int $seqNumber, string $mimeType, $file)
    {
        if ($this->fileError || is_null($this->dateTimeData)) {
            $directoryName = 'unsupported';
            $helper->createDirectoryIfMissing($directoryName);

            $newFilePath = $helper->getNewFilePath($directoryName, strtolower($file));
            rename($file, $newFilePath);

            $this->printFileInfo($helper, $mimeType, $newFilePath, $file);
        } else {
            $directoryName = $helper->extractYear($this->dateTimeData);
            $helper->createDirectoryIfMissing($directoryName);
            $paddedSeqNumber = str_pad(strval($seqNumber), 10, '0', STR_PAD_LEFT);
            $randomHex = bin2hex(random_bytes(3));

            if ($config->file_name_style !== 'short') {
                $helper->updateSequenceNumber($seqNumber);
            }

            $newFileName = $this->dateTimeData . '_' . $paddedSeqNumber . '_' . $randomHex . '.' . $this->fileExtension;

            switch ($config->file_name_style) {
                case 'short':
                    $newFileName = $this->dateTimeData . '_' . $randomHex . '.' . $this->fileExtension;
                    break;
                case 'medium':
                    $newFileName = $this->dateTimeData . '_' . $paddedSeqNumber . '.' . $this->fileExtension;
                    break;
            }

            $newFilePath = $helper->getNewFilePath($directoryName, $newFileName);
            rename($file, $newFilePath);

            $this->printFileInfo($helper, $mimeType, $newFilePath, $file);
        }

        $this->resetFileOptions();
    }
}
