<?php

declare(strict_types=1);

namespace ETS\Payment\OgoneBundle\Tests\Hash;

use ETS\Payment\OgoneBundle\Hash\Sha1In;
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

/** @author ETSGlobal <ecs@etsglobal.org> */
class Sha1InTest extends TestCase
{
    public function testGenerateUseUppercase(): void
    {
        $hashGenerator = new Sha1In($this->createTokenMock());

        $refSha1  = $hashGenerator->generate(['CN' => 'Foo Bar']);
        $testSha1 = $hashGenerator->generate(['cn' => 'Foo Bar']);

        $this->assertEquals($refSha1, $testSha1);
    }

    public function testGenerateSortKeys(): void
    {
        $hashGenerator = new Sha1In($this->createTokenMock());

        $refSha1  = $hashGenerator->generate(['PSPID' => 42, 'CN' => 'Foo Bar']);
        $testSha1 = $hashGenerator->generate(['CN' => 'Foo Bar', 'PSPID' => 42]);

        $this->assertEquals($refSha1, $testSha1);
    }

    public function testGenerateShouldSkipNotAllowedParameters(): void
    {
        $hashGenerator = new Sha1In($this->createTokenMock());

        $refSha1  = $hashGenerator->generate([]);
        $testSha1 = $hashGenerator->generate(['foo' => 'bar']);

        $this->assertEquals($refSha1, $testSha1);
    }

    public function testGenerateShouldAllowWildcardedParameters(): void
    {
        $hashGenerator = new Sha1In($this->createTokenMock());

        $refSha1  = $hashGenerator->generate([]);
        $testSha1 = $hashGenerator->generate(['ITEMNAME01' => 'foobar']);

        $this->assertNotEquals($refSha1, $testSha1);
    }

    /** @return \ETS\Payment\OgoneBundle\Client\TokenInterface */
    protected function createTokenMock()
    {
        return $this->createMock('ETS\Payment\OgoneBundle\Client\TokenInterface');
    }
}
