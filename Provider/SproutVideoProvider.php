<?php

namespace Ins\MediaApiBundle\Provider;

use Sonata\CoreBundle\Model\Metadata;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\BaseVideoProvider;
use SproutVideo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SproutVideoProvider extends BaseVideoProvider
{
    public static $STATE_TO_PROVIDERSTATUS = array(
        'inspecting' => MediaInterface::STATUS_PENDING,
        'processing' => MediaInterface::STATUS_ENCODING,
        'deployed' => MediaInterface::STATUS_OK,
        'failed' => MediaInterface::STATUS_ERROR,
    );

    public static $NOTIFICATION_URL;

    /**
     * @param array $configuration
     */
    public function setConfiguration($configuration)
    {
        SproutVideo::$api_key = $configuration['sproutvideo_apikey'];
        self::$NOTIFICATION_URL = $configuration['sproutvideo_notification_url'];
    }

    /**
     * {@inheritdoc}
     */
    public function getHelperProperties(MediaInterface $media, $format, $options = array())
    {
        $metadata = $media->getProviderMetadata();

        if (!count($metadata))
        {
            $this->updateMetadata($media);
            $metadata = $media->getProviderMetadata();
        }

        $params = [];
        switch ($format) {
            case 'sproutvideo_embed':
                $params['src'] = sprintf('https://videos.sproutvideo.com/embed/%s/%s?type=hd&playerColor=2f3437&playerTheme=light', $media->getProviderReference(), $metadata['security_token']);
                $params['width'] = $media->getWidth();
                $params['height'] = $media->getHeight();
                break;
            case 'sproutvideo_poster':
                $params['src'] = $metadata['assets']['poster_frames'][0];
                break;
            case 'sproutvideo_thumbnail':
                $params['src'] = $metadata['assets']['thumbnails'][1];
                break;
        }

        return $params;
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderMetadata()
    {
        return new Metadata($this->getName(), $this->getName().'.description', false, 'SonataMediaBundle', array('class' => 'fa fa-vimeo-square'));
    }

    /**
     * {@inheritdoc}
     */
    public function updateMetadata(MediaInterface $media, $force = false)
    {
        try {
            $metadata = $this->getMetadata($media, null);
        } catch (\RuntimeException $e) {
            $media->setEnabled(false);
            $media->setProviderStatus(MediaInterface::STATUS_ERROR);

            return;
        }

        // store provider information
        $media->setProviderMetadata($metadata);

        // update Media common fields from metadata
        if ($force) {
            $media->setName($metadata['title']);
            $media->setDescription(isset($metadata['description']) ? $metadata['description'] : null);
            $media->setAuthorName(isset($metadata['author_name']) ? $metadata['author_name'] : null);
        }

        $media->setProviderStatus(isset(self::$STATE_TO_PROVIDERSTATUS[$metadata['state']]) ? self::$STATE_TO_PROVIDERSTATUS[$metadata['state']] : self::$STATE_TO_PROVIDERSTATUS['Failed']);
        $media->setEnabled($media->getProviderStatus() === MediaInterface::STATUS_OK);
        $media->setHeight($metadata['height']);
        $media->setWidth($metadata['width']);
        $media->setLength($metadata['duration']);
    }

    /**
     * @throws \RuntimeException
     *
     * @param MediaInterface $media
     *
     * @return mixed
     */
    protected function getMetadata(MediaInterface $media, $url)
    {
        try {
            $metadata = SproutVideo\Video::get_video($media->getProviderReference());
        } catch (\RuntimeException $e) {
            throw new \RuntimeException('Unable to retrieve the video information for :'.$media->getProviderReference(), null, $e);
        }

        if (!$metadata) {
            throw new \RuntimeException('Unable to decode the video information for :'.$media->getProviderReference());
        }

        return $metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function getDownloadResponse(MediaInterface $media, $format, $mode, array $headers = array())
    {
        return new RedirectResponse(sprintf('https://sproutvideo.com/videos/%s', $media->getProviderReference()), 302, $headers);
    }

    /**
     * @param MediaInterface $media
     */
    protected function fixBinaryContent(MediaInterface $media)
    {
        if (!$media->getBinaryContent() && !$media->getBinaryContent() instanceof UploadedFile) {
            return;
        }

        $metadata = SproutVideo\Video::create_video($media->getBinaryContent()->getPathname(), array('title' => $media->getName(), 'privacy' => 0, 'hide_on_site' => true, 'notification_url' => self::$NOTIFICATION_URL));

        $media->setBinaryContent($metadata['id']);
    }

    /**
     * {@inheritdoc}
     */
    protected function doTransform(MediaInterface $media)
    {
        $this->fixBinaryContent($media);

        if (!$media->getBinaryContent()) {
            return;
        }

        // store provider information
        $media->setProviderName($this->name);
        $media->setProviderReference($media->getBinaryContent());
        $media->setProviderStatus(MediaInterface::STATUS_SENDING);

        $this->updateMetadata($media, true);
    }
}
