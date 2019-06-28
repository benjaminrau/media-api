<?php

namespace Ins\MediaApiBundle\Action;

use Ins\MediaApiBundle\Provider\SproutVideoProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sonata\MediaBundle\Entity\MediaManager;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;

class SproutVideoEventAction
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
	 * @var EventDispatcher
	 */
	private $eventDispatcher;

    /**
     * @var SproutVideoProvider
     */
    private $provider;

	public function __construct(Serializer $serializer, MediaManager $mediaManager, EventDispatcher $eventDispatcher, SproutVideoProvider $provider) {
		$this->serializer = $serializer;
		$this->mediaManager = $mediaManager;
		$this->eventDispatcher = $eventDispatcher;
        $this->provider = $provider;
	}

	/**
	 * @param Request $request
	 * @return Response
	 *
	 * @Route(
	 *     name="media_api_sprout_video_event",
	 *     path="/webhook/sproutvideo/event",
	 * )
	 * @Method("POST")
	 */
	public function __invoke(Request $request)
	{
		if ($request->getContentType() !== 'json')
		{
			return null;
		}

        $video = json_decode($request->getContent(), true);
        $mediaElement = $this->mediaManager->findOneBy(array('providerReference' => $video['id']));

        if ($mediaElement) {
            $this->provider->updateMetadata($mediaElement);
            $this->mediaManager->save($mediaElement, true);
        }

		return new Response(null, Response::HTTP_OK);
	}
}
