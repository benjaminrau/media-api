<?php

namespace Ins\MediaApiBundle\Thumbnail;

use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;
use Symfony\Component\Routing\RouterInterface;

class LiipImagineThumbnail implements ThumbnailInterface
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function generatePublicUrl(MediaProviderInterface $provider, MediaInterface $media, $format)
    {
        if ($format == 'reference') {
            $path = $provider->getCdnPath($provider->getReferenceImage($media), $media->getCdnIsFlushable());
        } else {
            $path = $this->router->generate(
                'liip_imagine_filter',
                array('filter' => $format, 'path' => sprintf('%s/%s_%s.jpg', substr($provider->getCdnPath($provider->generatePath($media), $media->getCdnIsFlushable()),1), $media->getId(), $format))
            );

        }

        return $path;
    }

    /**
     * {@inheritdoc}
     */
    public function generatePrivateUrl(MediaProviderInterface $provider, MediaInterface $media, $format)
    {
        if ($format != 'reference') {
            throw new \RuntimeException('No private url for LiipImagineThumbnail');
        }

        $path = $provider->getReferenceImage($media);

        return $path;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(MediaProviderInterface $provider, MediaInterface $media)
    {
        // nothing to generate, as generated on demand
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(MediaProviderInterface $provider, MediaInterface $media, $formats = null)
    {
        // feature not available
        return;
    }
}