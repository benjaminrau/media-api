<?php

namespace Ins\MediaApiBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Sonata\MediaBundle\Entity\BaseMedia;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
class MediaElement extends BaseMedia
{
	const PROVIDER_IMAGE = "sonata.media.provider.image";
	const PROVIDER_PDF_FILE = "sonata.media.provider.pdf";
	const PROVIDER_SPROUTVIDEO = "sonata.media.provider.sproutvideo";

	public static $MIMETYPE_TO_PROVIDER = array(
		'image/png' => self::PROVIDER_IMAGE,
		'image/gif' => self::PROVIDER_IMAGE,
		'image/jpg' => self::PROVIDER_IMAGE,
		'image/jpeg' => self::PROVIDER_IMAGE,
		'image/bmp' => self::PROVIDER_IMAGE,
		'application/pdf' => self::PROVIDER_PDF_FILE,
        'video/mp4' => self::PROVIDER_SPROUTVIDEO,
        'video/ogg' => self::PROVIDER_SPROUTVIDEO,
        'video/webm' => self::PROVIDER_SPROUTVIDEO,
        'video/mpeg' => self::PROVIDER_SPROUTVIDEO,
        'video/quicktime' => self::PROVIDER_SPROUTVIDEO,
        'video/x-msvideo' => self::PROVIDER_SPROUTVIDEO,
	);

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

	/**
	 * @var array
	 */
	protected $formats;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

	/**
	 * @return array
	 */
	public function getFormats() {
		return $this->formats;
	}

	/**
	 * @param array $format
	 */
	public function addFormat($format) {
		$this->formats[] = $format;
	}

	public static function isSupportedMimeType($mimeType) {
		return isset(self::$MIMETYPE_TO_PROVIDER[$mimeType]);
	}

	public static function getProviderForMimeType($mimeType) {
		return isset(self::$MIMETYPE_TO_PROVIDER[$mimeType]) ? self::$MIMETYPE_TO_PROVIDER[$mimeType] : null;
	}
}