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
			->end();

		return $treeBuilder;
	}
}
