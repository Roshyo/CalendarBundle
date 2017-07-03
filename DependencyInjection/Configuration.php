<?php

namespace Roshyo\PlanningBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root('roshyo_planning');
		
		/*$rootNode
			->children()
				->arrayNode('resources')
					->beforeNormalization()
					->ifTrue(function ($v) { return is_array($v); })
					->then(function ($v){
						$resources = [];
						foreach($v as $key => $value){
							$resources[$key] = $v[$key];
							unset($v[$key]);
						}
					})
			->end()
		;*/
		
		$rootNode
			->children()
			->append($this->createResourcesNode())
			->end();
		
		return $treeBuilder;
	}
	
	private function createResourcesNode()
	{
		$builder = new TreeBuilder();
		$node = $builder->root('resources');
		$node
			->useAttributeAsKey('name')
			->prototype('array')
			->children()
			->arrayNode('items')
			->prototype('scalar')->end()
			->end()
			->scalarNode('class')
			->defaultValue(null)
			->end()
			->end()
			->end();
		
		return $node;
	}
}
