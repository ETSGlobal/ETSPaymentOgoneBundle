<?php

namespace ETS\Payment\OgoneBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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
 * Bundle Extension class
 *
 * @author ETSGlobal <ecs@etsglobal.org>
 */
class ETSPaymentOgoneExtension extends Extension
{
    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $processor     = new Processor();
        $config        = $processor->processConfiguration($configuration, $configs);

        $xmlLoader     = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $xmlLoader->load('services.xml');

        $container->setParameter('payment.ogone.pspid', $config['pspid']);
        $container->setParameter('payment.ogone.shain', $config['shain']);
        $container->setParameter('payment.ogone.shaout', $config['shaout']);
        $container->setParameter('payment.ogone.debug', $config['debug']);
        $container->setParameter('payment.ogone.utf8', $config['utf8']);

        $container->setParameter('payment.ogone.api.user', $config['api']['user']);
        $container->setParameter('payment.ogone.api.password', $config['api']['password']);

        $container->setParameter('payment.ogone.redirection.accept_url', $config['redirection']['accept_url']);
        $container->setParameter('payment.ogone.redirection.decline_url', $config['redirection']['decline_url']);
        $container->setParameter('payment.ogone.redirection.exception_url', $config['redirection']['exception_url']);
        $container->setParameter('payment.ogone.redirection.cancel_url', $config['redirection']['cancel_url']);
        $container->setParameter('payment.ogone.redirection.back_url', $config['redirection']['back_url']);

        $container->setParameter('payment.ogone.design.tp', $config['design']['tp']);
        $container->setParameter('payment.ogone.design.title', $config['design']['title']);
        $container->setParameter('payment.ogone.design.bgColor', $config['design']['bgColor']);
        $container->setParameter('payment.ogone.design.txtColor', $config['design']['txtColor']);
        $container->setParameter('payment.ogone.design.tblBgColor', $config['design']['tblBgColor']);
        $container->setParameter('payment.ogone.design.tblTxtColor', $config['design']['tblTxtColor']);
        $container->setParameter('payment.ogone.design.buttonBgColor', $config['design']['buttonBgColor']);
        $container->setParameter('payment.ogone.design.buttonTxtColor', $config['design']['buttonTxtColor']);
        $container->setParameter('payment.ogone.design.fontType', $config['design']['fontType']);
        $container->setParameter('payment.ogone.design.logo', $config['design']['logo']);

        if (true === $config['mock_plugin']) {
            $container->setParameter('payment.plugin.ogone.class', $container->getParameter('payment.plugin.ogone.mock.class'));
        }
    }
}
