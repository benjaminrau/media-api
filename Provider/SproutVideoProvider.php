<?php

namespace Ins\MediaApiBundle\Provider;

use Behat\Testwork\ServiceContainer\Exception\ConfigurationLoadingException;
use Sonata\CoreBundle\Model\Metadata;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\BaseVideoProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SproutVideoProvider extends BaseVideoProvider
{

    private static $apiKey;

    /**
     * @param array $configuration
     */
    public function setConfiguration($configuration)
    {
        self::$apiKey = $configuration['sproutvideo_apikey'];
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

        $src = '';
        switch ($format) {
            case 'sproutvideo_embed':
                $src = sprintf('https://videos.sproutvideo.com/embed/%s/%s?type=hd&playerColor=2f3437&playerTheme=light', $media->getProviderReference(), $metadata['security_token']);
        }

        $params = array('src' => $src);
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
        $url = sprintf('https://api.sproutvideo.com/v1/videos/%s', $media->getProviderReference());

        try {
            $metadata = $this->getMetadata($media, $url);
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
            $media->setDescription($metadata['description']);
            $media->setAuthorName($metadata['author_name']);
        }

        $media->setHeight($metadata['height']);
        $media->setWidth($metadata['width']);
        $media->setLength($metadata['duration']);
    }

    /**
     * @throws \RuntimeException
     *
     * @param MediaInterface $media
     * @param string         $url
     *
     * @return mixed
     */
    protected function getMetadata(MediaInterface $media, $url)
    {
        try {
            $response = $this->browser->get($url, array('SproutVideo-Api-Key' => self::$apiKey));
        } catch (\RuntimeException $e) {
            throw new \RuntimeException('Unable to retrieve the video information for :'.$url, null, $e);
        }

        $metadata = json_decode($response->getContent(), true);

        if (!$metadata) {
            throw new \RuntimeException('Unable to decode the video information for :'.$url);
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
        if (!$media->getBinaryContent()) {
            return;
        }

        if (preg_match("/sproutvideo\.com\/(videos\/|)(\.+)/", $media->getBinaryContent(), $matches)) {
            $media->setBinaryContent($matches[2]);
        }
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
        $media->setProviderStatus(MediaInterface::STATUS_OK);

        $this->updateMetadata($media, true);
    }
}
