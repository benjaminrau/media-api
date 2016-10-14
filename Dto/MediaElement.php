<?php

namespace Ins\MediaApiBundle\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class MediaElement
{
	/**
	 * @var string
	 * @Assert\NotNull
	 */
    private $fileName;

	/**
	 * @var string
	 * @Assert\NotNull
	 */
	private $data;

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
	 * @return array
	 */
	private function extractAttributes() {
		$attributes = array(
			'base64Content' => '',
			'binaryContent' => '',
			'mimeType' => '',
			'fileExtension' => '',
		);

		if (!$this->getData())
		{
			return $attributes;
		}

		list($mimeType, $data) = explode(';', $this->getData());
		list(, $attributes['mimeType'])      = explode(':', $mimeType);
		list(, $attributes['fileExtension'])	  = explode('/', $mimeType);
		list(, $attributes['base64Content'])      = explode(',', $data);
		$attributes['binaryContent'] = base64_decode($attributes['base64Content']);

		return $attributes;
	}
}
