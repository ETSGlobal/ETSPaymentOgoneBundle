<?php

namespace ETS\Payment\OgoneBundle\Plugin;

use JMS\Payment\CoreBundle\BrowserKit\Request;
use Symfony\Component\BrowserKit\Response;

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
 * Ogone gateway plugin mock class for tests
 *
 * @author ETSGlobal <ecs@etsglobal.org>
 */
class OgoneGatewayPluginMock extends OgoneGatewayPlugin
{
    /**
     * @var string
     */
    protected $filename = 'deposited';

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    public function getFilename()
    {
        return sprintf('%s/../Resources/fixtures/%s.xml', __DIR__, $this->filename);
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
        if (file_exists($this->getFilename())) {
            return new Response(file_get_contents($this->getFilename()), 200);
        } else {
            return new Response('', 500);
        }
    }
}
