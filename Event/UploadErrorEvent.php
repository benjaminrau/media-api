<?php

namespace Ins\MediaApiBundle\Event;

use Ins\MediaApiBundle\Dto as Dto;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UploadEvent
 *
 * @package Ins\MediaApiBundle\Event
 */
class UploadErrorEvent extends Event
{
    const NAME = 'media_api.upload.error';

    /**
     * @var Dto\MediaElement
     */
    private $mediaElementDto;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * UploadErrorEvent constructor.
     *
     * @param Dto\MediaElement $mediaElementDto
     * @param Request $request
     * @param array $errors
     */
    public function __construct(Dto\MediaElement $mediaElementDto, Request $request, array $errors = [])
    {
        $this->mediaElementDto = $mediaElementDto;
        $this->request = $request;
        $this->errors = $errors;
    }

    /**
     * @return Dto\MediaElement
     */
    public function getMediaElementDto(): Dto\MediaElement
    {
        return $this->mediaElementDto;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
