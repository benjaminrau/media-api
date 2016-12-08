<?php

namespace Ins\MediaApiBundle\Action;

use Doctrine\Common\Persistence\ManagerRegistry;
use Ins\MediaApiBundle\Entity\MediaElement;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sonata\MediaBundle\Entity\MediaManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\DependencyInjection\Container;
use Ins\MediaApiBundle\Dto as Dto;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\VarDumper\VarDumper;

class UploadAction
{
	/**
	 * @var Serializer
	 */
	private $serializer;

	/**
	 * @var MediaManager
	 */
	private $mediaManager;

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var Router
	 */
	private $router;

	public function __construct(Serializer $serializer, MediaManager $mediaManager, Router $router, Container $container) {
		$this->serializer = $serializer;
		$this->mediaManager = $mediaManager;
		$this->router = $router;
		$this->container = $container;
	}

	/**
	 * @param Request $request
	 * @return Response
	 *
	 * @Route(
	 *     name="api_media_elements_post_collection",
	 *     path="/api/media_elements",
	 * )
	 * @Method("POST")
	 */
	public function __invoke(Request $request)
	{
		if ($request->getContentType() !== 'json')
		{
			return null;
		}

		/** @var Dto\MediaElement $mediaElementDto */
		$mediaElementDto = $this->serializer->deserialize($request->getContent(), Dto\MediaElement::class,  $request->getContentType());

		if (
			MediaElement::isSupportedMimeType($mediaElementDto->getMimeType()) &&
			$mediaElement = self::createMediaElement(
				$this->container->getParameter('sonata.media.media.class'),
				$mediaElementDto)
		) {
			$this->mediaManager->save($mediaElement);

			$buzz = $this->container->get('buzz');
			$buzzResponse = $buzz->get(
				$this->router->generate('api_media_elements_get_item', array('id' => $mediaElement->getId()), Router::ABSOLUTE_URL),
				['authorization' => $request->headers->get('authorization')]
			);

			return new JsonResponse($buzzResponse->getContent(), Response::HTTP_CREATED, $headers = array("Content-Type" => "application/ld+json"), true);
		}

		return new Response('', Response::HTTP_BAD_REQUEST);
	}

	/**
	 * @param string $class
	 * @param Dto\MediaElement $mediaElementDto
	 * @return MediaElement
	 */
	public static function createMediaElement($class, Dto\MediaElement $mediaElementDto) {
		/** @var MediaElement $mediaElement */
		$mediaElement = new $class;
		$mediaElement->setBinaryContent(self::createTempFile($mediaElementDto));

        $providerName = $mediaElement->getProviderForMimeType($mediaElementDto->getMimeType());
        $providerNameParts = explode('.', $providerName);

        $mediaElement->setName($mediaElementDto->getFileName());
		$mediaElement->setContext(end($providerNameParts));
		$mediaElement->setProviderName($providerName);
        $mediaElement->setContentType($mediaElementDto->getMimeType());

		return $mediaElement;
	}

	/**
	 * @param Dto\MediaElement $mediaElementDto
	 * @return UploadedFile
	 */
	private static function createTempFile(Dto\MediaElement $mediaElementDto) {
		if (!$binaryContent = $mediaElementDto->getBinaryContent())
		{
			return false;
		}

		$temporaryFileName = tempnam(sys_get_temp_dir(), 'upload_action_') . "." . pathinfo($mediaElementDto->getFileName(), PATHINFO_EXTENSION);

		file_put_contents($temporaryFileName, $binaryContent);

		return new UploadedFile(
			$temporaryFileName,
			$mediaElementDto->getFileName()
		);
	}
}
