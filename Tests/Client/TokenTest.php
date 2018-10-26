<?php

namespace ETS\Payment\OgoneBundle\Tests\Client;

use ETS\Payment\OgoneBundle\Client\Token;
use PHPUnit\Framework\TestCase;

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
 * Token class test
 *
 * @author ETSGlobal <ecs@etsglobal.org>
 */
class TokenTest extends TestCase
{
    /**
     * Test the object construction
     */
    public function testConstruct()
    {
        $pspid = 'foobar';
        $apiPassword = 'fooapipass';
        $apiUser = 'fooapiuser';
        $shain = 'fooshain';
        $shaout= 'fooshaout';

        $token = new Token($pspid, $apiUser, $apiPassword, $shain, $shaout);

        $this->assertEquals($pspid, $token->getPspid());
        $this->assertEquals($apiUser, $token->getApiUser());
        $this->assertEquals($apiPassword, $token->getApiPassword());
        $this->assertEquals($shain, $token->getShain());
        $this->assertEquals($shaout, $token->getShaout());
    }
}
