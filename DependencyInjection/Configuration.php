<?php

namespace ETS\Payment\OgoneBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/*
 * Copyright 2013 ETSGlobal <ecs@etsglobal.org>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Bundle Configuration
 *
 * @author ETSGlobal <ecs@etsglobal.org>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        return $treeBuilder
            ->root('ets_payment_ogone','array')
                ->children()
                    ->scalarNode('pspid')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('shain')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('shaout')->isRequired()->cannotBeEmpty()->end()
                    ->booleanNode('debug')->defaultValue('%kernel.debug%')->end()
                    ->booleanNode('utf8')->defaultValue(false)->end()
                    ->arrayNode('api')
                        ->isRequired()
                        ->children()
                            ->scalarNode('user')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('password')->isRequired()->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                    ->arrayNode('redirection')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('accept_url')->defaultNull()->end()
                            ->scalarNode('decline_url')->defaultNull()->end()
                            ->scalarNode('exception_url')->defaultNull()->end()
                            ->scalarNode('cancel_url')->defaultNull()->end()
                            ->scalarNode('back_url')->defaultNull()->end()
                        ->end()
                    ->end()
                    ->arrayNode('design')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('tp')->defaultNull()->end()
                            ->scalarNode('title')->defaultNull()->end()
                            ->scalarNode('bgColor')->defaultNull()->end()
                            ->scalarNode('txtColor')->defaultNull()->end()
                            ->scalarNode('tblBgColor')->defaultNull()->end()
                            ->scalarNode('tblTxtColor')->defaultNull()->end()
                            ->scalarNode('buttonBgColor')->defaultNull()->end()
                            ->scalarNode('buttonTxtColor')->defaultNull()->end()
                            ->scalarNode('fontType')->defaultNull()->end()
                            ->scalarNode('logo')->defaultNull()->end()
                        ->end()
                    ->end()
                    ->booleanNode('mock_plugin')->defaultValue('false')->end()
                ->end()
            ->end();
    }
}
