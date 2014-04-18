<?php

namespace ETS\Payment\OgoneBundle\Tests\Hash;

use ETS\Payment\OgoneBundle\Hash\Sha1In;

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
 * Sha-1 In test
 *
 * @author ETSGlobal <ecs@etsglobal.org>
 */
class Sha1InTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that the generate function uppercase the keys
     */
    public function testGenerateUseUppercase()
    {
        $hashGenerator = new Sha1In($this->createTokenMock());

        $refSha1  = $hashGenerator->generate(array('CN' => 'Foo Bar'));
        $testSha1 = $hashGenerator->generate(array('cn' => 'Foo Bar'));

        $this->assertEquals($refSha1, $testSha1);
    }

    /**
     * Test that the generate function sort key alphabetically
     */
    public function testGenerateSortKeys()
    {
        $hashGenerator = new Sha1In($this->createTokenMock());

        $refSha1  = $hashGenerator->generate(array('PSPID' => 42, 'CN' => 'Foo Bar'));
        $testSha1 = $hashGenerator->generate(array('CN' => 'Foo Bar', 'PSPID' => 42));

        $this->assertEquals($refSha1, $testSha1);
    }

    /**
     * Test that the generate function only use allowed parameters
     */
    public function testGenerateShouldSkipNotAllowedParameters()
    {
        $hashGenerator = new Sha1In($this->createTokenMock());

        $refSha1  = $hashGenerator->generate(array());
        $testSha1 = $hashGenerator->generate(array('foo' => 'bar'));

        $this->assertEquals($refSha1, $testSha1);
    }

    /**
     * Test that the generate function take care of wildcarded parameters
     */
    public function testGenerateShouldAllowWildcardedParameters()
    {
        $hashGenerator = new Sha1In($this->createTokenMock());

        $refSha1  = $hashGenerator->generate(array());
        $testSha1 = $hashGenerator->generate(array('ITEMNAME01' => 'foobar'));

        $this->assertNotEquals($refSha1, $testSha1);
    }

    /**
     * @return \ETS\Payment\OgoneBundle\Client\TokenInterface
     */
    protected function createTokenMock()
    {
        return $this->getMock('ETS\Payment\OgoneBundle\Client\TokenInterface');
    }
}
