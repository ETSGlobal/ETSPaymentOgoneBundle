<?php

namespace ETS\Payment\OgoneBundle\Tests\Hash;

use ETS\Payment\OgoneBundle\Hash\Sha1Out;

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
 * Sha-1 In test
 *
 * @author ETSGlobal <e4-devteam@etsglobal.org>
 */
class Sha1OutTest extends \PHPUnit_Framework_TestCase
{
    public function testGetStringToHashWithZeroAsParamValue()
    {
        $params = array(
            'CURRENCY' => 'EUR',
            'PAYID'    => 123456,
            'NCERROR'  => 0,
        );

        $sha1outGen = new Sha1Out('passphrase');

        $class = new \ReflectionClass($sha1outGen);
        $getStringToHashMethod = $class->getMethod('getStringToHash');
        $getStringToHashMethod->setAccessible(true);

        $stringToHash = $getStringToHashMethod->invokeArgs($sha1outGen, array($params));

        $this->assertTrue(false !== strpos($stringToHash, 'NCERROR'), 'fields must be included for hash calculation even if their value is 0');
    }
}
