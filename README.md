# Media API - Example integration of Sonata Media Bundle with ApiPlatform
Example for integration of Sonata MediaBundle with ApiPlatform 2.0 including media upload and exposing media formats in GET item operation

## Dependencies

- Symfony 3.1
- Sonata MediaBundle
- Dunglas ActionBundle

## Setup

- Clone this repository to your src/ folder of your symfony instance
- Create a MediaElement entity without your AppBundle and extend from Ins\MediaApiBundle\Entity\MediaElement
- Configure serialization groups according to your needs
- Configure Sonata MediaBundle to use your MediaElement class
- Add routing configuration for UploadAction

### app/config/config.yml

```
sonata_media:
    class:
        media: YourBundle\Entity\MediaElement
```

### app/config/routing.yml

```
media_action:
    resource: '@MediaApiBundle/Action/'
    type:     'annotation'
```

### src/YourBundle/Entity/MediaElement.php

```
<?php

namespace YourBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Ins\MediaApiBundle\Entity\MediaElement as BaseMediaElement;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiResource;

/**
 * @ApiResource(
 * 		itemOperations={
 * 			"get"={"method"="GET"}
 * 		},
 * 		collectionOperations={
 * 			"get"={"method"="GET"}
 *	 	}
 * )
 * @ORM\Entity
 * @ORM\Table(name="MediaElement")
 */
class MediaElement extends BaseMediaElement
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="guid")
	 * @ORM\GeneratedValue(strategy="UUID")
	 */
	protected $id;

    /**
     * @var string
     * @Groups({"api_mediaelement_get_item", "api_article_get_item", "api_article_get_collection"})
     */
    protected $formats;

	public function canUserView(TokenInterface $token) {
		return true;
	}
}
```