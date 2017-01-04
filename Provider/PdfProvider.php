<?php

namespace Ins\MediaApiBundle\Provider;

use Gaufrette\Filesystem;
use Imagine\Image\ImagineInterface;
use Sonata\MediaBundle\CDN\CDNInterface;
use Sonata\MediaBundle\Generator\GeneratorInterface;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\FileProvider;
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PdfProvider extends FileProvider
{
    protected $imagineAdapter;
    protected $container;

    /**
     * @param string                                                $name
     * @param \Gaufrette\Filesystem                                 $filesystem
     * @param \Sonata\MediaBundle\CDN\CDNInterface                  $cdn
     * @param \Sonata\MediaBundle\Generator\GeneratorInterface      $pathGenerator
     * @param \Sonata\MediaBundle\Thumbnail\ThumbnailInterface      $thumbnail
     * @param array                                                 $allowedExtensions
     * @param array                                                 $allowedMimeTypes
     * @param \Imagine\Image\ImagineInterface                       $adapter
     * @param \Sonata\MediaBundle\Metadata\MetadataBuilderInterface $metadata
     * @param ContainerInterface $container
     */
    public function __construct($name, Filesystem $filesystem, CDNInterface $cdn, GeneratorInterface $pathGenerator, ThumbnailInterface $thumbnail, array $allowedExtensions = array(), array $allowedMimeTypes = array(), ImagineInterface $adapter, MetadataBuilderInterface $metadata = null, ContainerInterface $container)
    {
        parent::__construct($name, $filesystem, $cdn, $pathGenerator, $thumbnail, $allowedExtensions, $allowedMimeTypes, $metadata);
        $this->imagineAdapter = $adapter;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function generatePublicUrl(MediaInterface $media, $format)
    {
        if ($format == 'reference') {
            $path = $this->getCdn()->getPath($this->getReferenceImage($media), $media->getCdnIsFlushable());
        } else {
            $path = $this->thumbnail->generatePublicUrl($this, $media, $format);
        }
        return $path;
    }

    /**
     * {@inheritdoc}
     */
    public function generatePrivateUrl(MediaInterface $media, $format)
    {
        return $this->thumbnail->generatePrivateUrl($this, $media, $format);
    }

    /**
     * {@inheritdoc}
     */
    public function getHelperProperties(MediaInterface $media, $format, $options = array())
    {

        $settings = $this->getFormat($format);

        if (isset($options['width']))
            $settings['width'] = $options['width'];

        if (isset($options['height']))
            $settings['height'] = $options['height'];

        $src = $this->generatePublicUrl($media, $format);
        
        $file     = $this->getReferenceFile($media);
        $path_pdf = $this->getCdnPath($this->getReferenceImage($media), $media->getCdnIsFlushable());
        $path_img = pathinfo($path_pdf, PATHINFO_DIRNAME) . '/' . pathinfo($path_pdf, PATHINFO_FILENAME) . '.jpg';
        $tmpFile = sprintf('%s.%s', tempnam(sys_get_temp_dir(), 'sonata_media_liip_imagine'), $media->getExtension());
        
        if (!file_exists($this->container->getParameter('kernel.root_dir').'/../web'.$path_img)) {
            file_put_contents($tmpFile, $file->getContent());
            exec("convert -density 72x72 -quality 100 {$tmpFile}[0] {$this->container->getParameter('kernel.root_dir')}/../web{$path_img}");
        }

        return array_merge(array(
            'alt'       => $media->getName(),
            'title'     => $media->getName(),
            'src'       => $this->getCdnPath($this->getReferenceImage($media), true),
            'thumbnail' => $src,
            'width'     => $settings['width'],
            'height'    => $settings['height']
        ), $options);
    }
}
