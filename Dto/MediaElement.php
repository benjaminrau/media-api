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
		$attributes = $this->extractAttributes();
		return $attributes['mimeType'];
	}

	public function getBinaryContent() {
		$attributes = $this->extractAttributes();
		return $attributes['binaryContent'];
	}

	public function getBase64Content() {
		$attributes = $this->extractAttributes();
		return $attributes['base64Content'];
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
		$attributes = array(
			'base64Content' => '',
			'binaryContent' => '',
			'mimeType' => '',
			'fileExtension' => '',
		);

		if ($this->getData()) {

            list($mimeType, $data) = explode(';', $this->getData());
            list(, $attributes['mimeType'])      = explode(':', $mimeType);
            list(, $attributes['fileExtension'])	  = explode('/', $mimeType);
            list(, $attributes['base64Content'])      = explode(',', $data);
            $attributes['binaryContent'] = base64_decode($attributes['base64Content']);

		} elseif ($this->getFile()) {

            $binaryContent = file_get_contents($this->getFile()->getPathname());
            $attributes['binaryContent'] = $binaryContent;
            $attributes['base64Content'] = base64_encode($binaryContent);
            $attributes['mimeType'] = $this->getFile()->getMimeType();
            list(, $attributes['fileExtension'])	  = explode('/', $this->getFile()->getMimeType());
        }

		return $attributes;
	}
}
