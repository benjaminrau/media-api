<?php

namespace Ins\MediaApiBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

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

        $container->setParameter('media_api.upload_max_filesize',$processedConfiguration['upload_max_filesize']);

        $this->configurePdfProvider($container, $processedConfiguration);
	}

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param array                                                   $config
     */
    public function configurePdfProvider(ContainerBuilder $container, $config)
    {
        $definition = $container->getDefinition('sonata.media.provider.pdf');
        $config = $config['providers']['pdf'];

        $definition
            ->replaceArgument(1, new Reference($config['filesystem']))
            ->replaceArgument(2, new Reference($config['cdn']))
            ->replaceArgument(3, new Reference($config['generator']))
            ->replaceArgument(4, new Reference($config['thumbnail']))
            ->replaceArgument(5, array_map('strtolower', $config['allowed_extensions']))
            ->replaceArgument(6, $config['allowed_mime_types'])
            ->replaceArgument(7, new Reference($config['adapter']))
        ;

        if ($config['resizer']) {
            $definition->addMethodCall('setResizer', array(new Reference($config['resizer'])));
        }
    }
}
