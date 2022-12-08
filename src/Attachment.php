<?php

namespace Quicksilver;

class Attachment
{
    /**
     * @var string|string
     */
    private $fileName;
    /**
     * @var string
     */
    private $filePath;

    /**
     * @param string  $fileName
     * @param string  $filePath
     */
    public function __construct(string $fileName, string $filePath)
    {
        $this->fileName = $fileName;
        $this->filePath = $filePath;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @param string  $fileName
     *
     * @return Attachment
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * @param string  $filePath
     *
     * @return Attachment
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
        return $this;
    }

}