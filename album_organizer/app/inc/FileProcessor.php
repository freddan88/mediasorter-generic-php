<?php

class FileProcessor
{
    private $dateTimeData = null;
    private $fileExtension = '';
    private $fileError = false;

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
    public function processFile(Helper $helper, int $seqNumber, string $mimeType, $file)
    {
        if ($this->fileError || is_null($this->dateTimeData)) {
            $directoryName = 'unsupported';
            $helper->createDirectoryIfMissing($directoryName);

            $newFilePath = $helper->getNewFilePath($directoryName, strtolower($file));
            rename($file, $newFilePath);
            echo PHP_EOL;

            $helper->printMessage("Old file-path: ./$file");
            $helper->printMessage("New file-path: ./$newFilePath");
        } else {
            $directoryName = $helper->extractYear($this->dateTimeData);
            $helper->createDirectoryIfMissing($directoryName);

            $paddedSeqNumber = str_pad($seqNumber, 8, 0, STR_PAD_LEFT);
            $helper->updateSequenceNumber($seqNumber);
            $randomHex = bin2hex(random_bytes(2));

            $newFileName = $this->dateTimeData . '_' . $randomHex . '_' . $paddedSeqNumber . '.' . $this->fileExtension;
            // $newFileName = $this->dateTimeData . '_' . $paddedSeqNumber . '.' . $this->$fileExtension;

            $newFilePath = $helper->getNewFilePath($directoryName, $newFileName);
            rename($file, $newFilePath);

            echo PHP_EOL;
            $helper->printMessage("------------------------------------");
            $helper->printMessage("File extension: $this->fileExtension");
            $helper->printMessage("File mime-type: $mimeType");
            echo PHP_EOL;
            $helper->printMessage("Old file-path: ./$file");
            $helper->printMessage("New file-path: ./$newFilePath");
            $helper->printMessage("------------------------------------");
        }

        $this->resetFileOptions();
    }
}
