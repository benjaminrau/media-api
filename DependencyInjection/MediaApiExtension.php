<?php

namespace Ins\MediaApiBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class MediaApiExtension extends Extension
{
	/**
	 * {@inheritDoc}
	 */
	public function load(array $configs, ContainerBuilder $container)
	{
        $configuration = new Configuration();
        $processedConfiguration = $this->processConfiguration($configuration, $configs);

		$loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
		$loader->load('services.yml');

		$container
			->register('Ins\MediaApiBundle\Action\UploadAction', 'Ins\MediaApiBundle\Action\UploadAction')
			->setAutowired(true);

        $container
            ->register('Ins\MediaApiBundle\Action\SproutVideoEventAction', 'Ins\MediaApiBundle\Action\SproutVideoEventAction')
            ->setAutowired(true);

        $definition = $container->getDefinition('sonata.media.provider.sproutvideo');
        $definition->addMethodCall('setConfiguration', array($processedConfiguration));
	}
}
