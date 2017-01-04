<?php
namespace Ins\MediaApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();
		$treeBuilder
			->root('media_api')
			->children()
				->scalarNode('sproutvideo_apikey')->end()
				->scalarNode('sproutvideo_notification_url')->end()
				->scalarNode('upload_max_filesize')->defaultValue('50M')->end()
                ->arrayNode('providers')
                    ->addDefaultsIfNotSet()
                    ->children()

                        ->arrayNode('pdf')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('service')->defaultValue('sonata.media.provider.pdf')->end()
                                ->scalarNode('resizer')->defaultValue('sonata.media.resizer.simple')->end()
                                ->scalarNode('filesystem')->defaultValue('sonata.media.filesystem.local')->end()
                                ->scalarNode('cdn')->defaultValue('sonata.media.cdn.server')->end()
                                ->scalarNode('generator')->defaultValue('sonata.media.generator.default')->end()
                                ->scalarNode('thumbnail')->defaultValue('sonata.media.thumbnail.liip_imagine')->end()
                                ->scalarNode('adapter')->defaultValue('sonata.media.adapter.image.gd')->end()
                                ->arrayNode('allowed_extensions')
                                    ->prototype('scalar')->end()
                                    ->defaultValue(array('pdf'))
                                ->end()
                                ->arrayNode('allowed_mime_types')
                                    ->prototype('scalar')->end()
                                    ->defaultValue(array(
                                        'application/pdf'
                                    ))
                                ->end()
                            ->end()
                        ->end()

                    ->end()
                ->end()
			->end();

		return $treeBuilder;
	}
}
