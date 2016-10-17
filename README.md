# Media API - Example integration of Sonata Media Bundle with ApiPlatform
Example for integration of Sonata MediaBundle with ApiPlatform 2.0 including media upload and exposing media formats in GET item operation

## Dependencies

- Symfony 3.1
- Sonata MediaBundle
- Cweagans Composer Patches

## Setup

- Clone this repository to your src/ folder of your symfony instance
- Create a MediaElement entity without your AppBundle and extend from Ins\MediaApiBundle\Entity\MediaElement
- Configure serialization groups according to your needs
- Configure Sonata MediaBundle to use your MediaElement class
- Add routing configuration for UploadAction

### Your projects main composer.json

If you have Sonata Classification configured and enabled, you can skip this change. There are some hard dependencies to Sonata Classification Bundle in Media Bundle yet, most of them are resolved in current GIT master, but some are left. Enable patches, to apply the patch we provide to resolve the last dependency, and to use MediaBundle without Classification Bundle.

```
{ 
  "extra": {
      "enable-patching": true
  }
}
```

### app/config/config.yml

Tell Sonata MediaBundle which Entity to use from your Bundle for Media elements.

```
sonata_media:
    class:
        media: YourBundle\Entity\MediaElement
```

### app/config/routing.yml

Load the routing for upload action that is configured in UploadAction class as annotation on __invoke() method.

```
media_action:
    resource: '@MediaApiBundle/Action/'
    type:     'annotation'
```

### src/YourBundle/Entity/MediaElement.php

Setup your Media entitiy. If you are using this with Api Platform and Serialization Groups, you will have to define all inherited properties from extended class in your entity, to define serialization groups.
In this example we define $formats property which is already declared in extended class, to add some serialization groups.

```php
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
}
```

## Example

### POST Request

```
POST /api/media_elements HTTP/1.1
Host: api.devloc.site
Content-Type: application/json

{
	"fileName": "testbild-83.png",
	"data": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABaQAAASMCAYAAAB3UopLAAAMF2lDQ1BJQ0MgUHJvZmlsZQAASImVlwdUU0kXx+eVFEJCC0RASuhNkF6l9yIgHWyEJEAoIQSCih1ZVHAtqFiwoisgCq4FkLUiioVFsPcNKirKuliwofJNEkD"
}
```

### GET Request

This GET item operation is fully served by Api Platform. Media Api Bundle is not taking care of it, we only intercept postLoad event to set formats property in your Media entity.

```
GET /api/media_elements/f71e2f38-9230-11e6-b60a-ca5a65ec716d HTTP/1.1
Host: api.devloc.site
```

### Response

```json
{
  "@context": "/api/contexts/MediaElement",
  "@id": "/api/media_elements/f71e2f38-9230-11e6-b60a-ca5a65ec716d",
  "@type": "MediaElement",
  "formats": [
    {
      "alt": "testbild-83.png",
      "title": "testbild-83.png",
      "src": "https://api.devloc.site/uploads/media/default/0001/01/thumb_f71e2f38-9230-11e6-b60a-ca5a65ec716d_default_small.png",
      "width": 79,
      "height": 64,
      "context": "default",
      "format": "small"
    },
    {
      "alt": "testbild-83.png",
      "title": "testbild-83.png",
      "src": "https://api.devloc.site/uploads/media/default/0001/01/thumb_f71e2f38-9230-11e6-b60a-ca5a65ec716d_default_medium.png",
      "width": 200,
      "height": 161,
      "context": "default",
      "format": "medium"
    },
    {
      "alt": "testbild-83.png",
      "title": "testbild-83.png",
      "src": "https://api.devloc.site/uploads/media/default/0001/01/thumb_f71e2f38-9230-11e6-b60a-ca5a65ec716d_default_large.png",
      "width": 799,
      "height": 644,
      "context": "default",
      "format": "large"
    }
  ]
}
```