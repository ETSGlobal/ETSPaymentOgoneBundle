<?php

namespace ETS\Payment\OgoneBundle\Tests\Hash;

use ETS\Payment\OgoneBundle\Hash\Sha1Out;

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
class Sha1OutTest extends \PHPUnit_Framework_TestCase
{
    public function testGetStringToHashFollowsOgoneRules()
    {
        $params = array(
            'PayId'    => 123456,
            'CURRENCY' => 'EUR',
            'NCERROR'  => 0,
            'BRAND'    => '',
        );

        $sha1outGen = new Sha1Out($this->createTokenMock());

        $class = new \ReflectionClass($sha1outGen);
        $getStringToHashMethod = $class->getMethod('getStringToHash');
        $getStringToHashMethod->setAccessible(true);

        $stringToHash = $getStringToHashMethod->invokeArgs($sha1outGen, array($params));

        $this->assertTrue(false !== strpos($stringToHash, 'NCERROR'), 'Fields must be included for hash calculation even if their value is 0.');
        $this->assertTrue(false === strpos($stringToHash, 'BRAND'), 'Parameters that do not have a value should NOT be included in the string to hash.');
        $this->assertTrue(false !== strpos($stringToHash, 'PAYID'), 'Each parameter must be put in upper case.');

        $firstParamPos  = strpos($stringToHash, 'CURRENCY');
        $secondParamPos = strpos($stringToHash, 'NCERROR');
        $thirdParamPos  = strpos($stringToHash, 'PAYID');
        $this->assertGreaterThan($firstParamPos, $secondParamPos, 'All parameters must be sorted following the order in Sha1Out::$acceptableFields.');
        $this->assertGreaterThan($secondParamPos, $thirdParamPos, 'All parameters must be sorted following the order in Sha1Out::$acceptableFields.');
    }

    public function testGenerate()
    {
        $params = array(
            'PayId'    => 123456,
            'CURRENCY' => 'EUR',
            'NCERROR'  => 0,
            'BRAND'    => '',
        );

        $sha1outGen = new Sha1Out($this->createTokenMock());

        $this->assertEquals('236FC768128A1104F949912E67ADFD4F2ED54341', $sha1outGen->generate($params), 'Generated hash is different from expected.');
    }

    /**
     * @return \ETS\Payment\OgoneBundle\Client\TokenInterface
     */
    protected function createTokenMock()
    {
        $tokenMock = $this->getMock('ETS\Payment\OgoneBundle\Client\TokenInterface');
        $tokenMock->expects($this->any())
            ->method('getShaout')
            ->will($this->returnValue('passphrase'));

        return $tokenMock;
    }
}
