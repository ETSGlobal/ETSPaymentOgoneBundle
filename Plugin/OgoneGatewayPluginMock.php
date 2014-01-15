<?php

namespace ETS\Payment\OgoneBundle\Plugin;

use JMS\Payment\CoreBundle\BrowserKit\Request;
use JMS\Payment\CoreBundle\Model\ExtendedDataInterface;
use Symfony\Component\BrowserKit\Response;

use ETS\Payment\OgoneBundle\Client\TokenInterface;
use ETS\Payment\OgoneBundle\Hash\HashGenerator;

/*
 * Copyright 2013 ETSGlobal <e4-devteam@etsglobal.org>
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
 * Ogone gateway plugin mock class for tests
 *
 * @author ETSGlobal <e4-devteam@etsglobal.org>
 */
class OgoneGatewayPluginMock extends OgoneGatewayPlugin
{
    /**
     * @var string
     */
    protected $filename;

    /**
     * @param TokenInterface            $token
     * @param HashGenerator             $hashGenerator
     * @param Configuration\Redirection $redirectionConfig
     * @param Configuration\Design      $designConfig
     * @param boolean                   $debug
     * @param boolean                   $utf8
     * @param string                    $filename
     */
    public function __construct(TokenInterface $token, HashGenerator $hashGenerator, Configuration\Redirection $redirectionConfig, Configuration\Design $designConfig, $debug, $utf8, $filename)
    {
        parent::__construct($token, $hashGenerator, $redirectionConfig, $designConfig, $debug, $utf8);

        $this->filename = $filename;
    }

    /**
     * Performs a request to an external payment service
     *
     * @param Request $request
     *
     * @return Response
     */
    public function request(Request $request)
    {
        if (file_exists($this->filename)) {
            return new Response(file_get_contents($this->filename), 200);
        } else {
            return new Response('', 500);
        }
    }
}
