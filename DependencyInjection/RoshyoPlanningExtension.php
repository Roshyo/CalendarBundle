<?php

namespace Roshyo\PlanningBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class RoshyoPlanningExtension extends Extension
{
	/** @var  array */
	private $resources;
	
	/**
	 * {@inheritdoc}
	 */
	public function load(array $configs, ContainerBuilder $container)
	{
		$configuration = new Configuration();
		$config = $this->processConfiguration($configuration, $configs);
		
		$resources = [];
		foreach($config['resources'] as $resourceName => $resource){
			/** @var \Roshyo\PlanningBundle\Calendar\Resources\Resource $resourceObject */
			$resourceObject = new $resource['class'];
			foreach($resource['items'] as $item){
				$resourceObject->addItemClass($item);
			}
			$resources[$resourceName] = $resourceObject;
		}
		
		$this->resources = $resources;
		
		$loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
		$loader->load('services.yml');
	}
}
