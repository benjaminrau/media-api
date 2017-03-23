<?php

namespace Ins\MediaApiBundle\Dto;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Ins\MediaApiBundle\Validator\Constraints as MediaApiAssert;

class MediaElement
{
    /**
     * @var string
     * @Assert\NotNull
     */
    private $fileName;

    /**
     * @var UploadedFile
     * @Assert\NotBlank
     * @MediaApiAssert\File
     */
    private $file;

    /**
     * @var string
     * @Assert\NotNull
     */
    private $data;

    /**
     * @var array | null
     */
    private $attributes = null;

    /**
     * @param string $fileName
     * @param string $data
     */
    function __construct($fileName = '', $data = '') {
        $this->data = $data;
        $this->fileName = $fileName;
    }

    /**
     * @return string
     */
    public function getData() {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData($data) {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getFileName() {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     */
    public function setFileName($fileName) {
        $this->fileName = $fileName;
    }

    public function getMimeType() {
        if (!$this->attributes) {
            $this->extractAttributes();
        }
        return $this->attributes['mimeType'];
    }

    public function getBinaryContent() {
        if (!$this->attributes) {
            $this->extractAttributes();
        }
        return $this->attributes['binaryContent'];
    }

    public function getBase64Content() {
        if (!$this->attributes) {
            $this->extractAttributes();
        }
        return $this->attributes['base64Content'];
    }

    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file)
    {
        $this->file = $file;
        $this->fileName = $file->getClientOriginalName();
    }

    /**
     * @return array
     */
    private function extractAttributes() {
        $this->attributes = array(
            'base64Content' => '',
            'binaryContent' => '',
            'mimeType' => '',
            'fileExtension' => '',
        );

        if ($this->data) {

            list($mimeType, $data) = explode(';', $this->getData());
            list(, $this->attributes['mimeType'])      = explode(':', $mimeType);
            list(, $this->attributes['fileExtension'])	  = explode('/', $mimeType);
            list(, $this->attributes['base64Content'])      = explode(',', $data);
            $this->attributes['binaryContent'] = base64_decode($this->attributes['base64Content']);

        } elseif ($this->file) {

            $binaryContent = file_get_contents($this->getFile()->getPathname());
            $this->attributes['binaryContent'] = $binaryContent;
            $this->attributes['base64Content'] = base64_encode($binaryContent);
            $this->attributes['mimeType'] = $this->getFile()->getMimeType();
            list(, $this->attributes['fileExtension'])	  = explode('/', $this->getFile()->getMimeType());
        }
    }
}
