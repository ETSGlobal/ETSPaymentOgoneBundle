<?php

namespace ETS\Payment\OgoneBundle\Plugin;

use Symfony\Component\BrowserKit\Response;
use ETS\Payment\OgoneBundle\Client\TokenInterface;
use ETS\Payment\OgoneBundle\Tools\ShaIn;

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
     * @param ShaIn                     $shaInTool
     * @param Configuration\Redirection $redirectionConfig
     * @param Configuration\Design      $designConfig
     * @param boolean                   $debug
     * @param string                    $filename
     */
    public function __construct(TokenInterface $token, ShaIn $shaInTool, Configuration\Redirection $redirectionConfig, Configuration\Design $designConfig, $debug, $filename)
    {
        parent::__construct($token, $shaInTool, $redirectionConfig, $designConfig, $debug);

        $this->filename = $filename;
    }

    /**
     * Performs a request to an external payment service
     *
     * @param Request $request
     * @param mixed $parameters either an array for form-data, or an url-encoded string
     *
     * @throws CommunicationException when an curl error occurs
     * @return Response
     */
    public function request(Request $request)
    {
        return new Response(file_get_contents($this->filename), 200);
    }
}
