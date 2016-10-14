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

		if (MediaElement::isSupportedMimeType($mediaElementDto->getMimeType()) && $uploadedFile = $this->createTempFile($mediaElementDto)) {
			$class = $this->container->getParameter('sonata.media.media.class');
			$mediaElement = new $class;
			$mediaElement->setBinaryContent($uploadedFile);
			$mediaElement->setContext('default');
			$mediaElement->setProviderName($mediaElement->getProviderForMimeType($mediaElementDto->getMimeType()));
			$this->mediaManager->save($mediaElement);

			return new RedirectResponse($this->router->generate('api_media_elements_get_item', array('id' => $mediaElement->getId())));
		}

		return new Response('', Response::HTTP_BAD_REQUEST);
	}

	/**
	 * @param Dto\MediaElement $mediaElementDto
	 * @return UploadedFile
	 */
	private function createTempFile(Dto\MediaElement $mediaElementDto) {
		if (!$binaryContent = $mediaElementDto->getBinaryContent())
		{
			return false;
		}

		$temporaryFileName = tempnam(sys_get_temp_dir(), 'upload_action_');

		file_put_contents($temporaryFileName, $binaryContent);

		return new UploadedFile(
			$temporaryFileName,
			$mediaElementDto->getFileName()
		);
	}
}
