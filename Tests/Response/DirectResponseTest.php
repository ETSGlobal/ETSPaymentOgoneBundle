<?php

namespace ETS\Payment\OgoneBundle\Tests\Response;

use ETS\Payment\OgoneBundle\Response\DirectResponse;
use ETS\Payment\OgoneBundle\Response\ResponseInterface;

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
 * Response test class
 *
 * @author ETSGlobal <ecs@etsglobal.org>
 */
class DirectResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test an invalid response
     */
    public function testInvalidXml()
    {
        $response = $this->createResponse('invalid');

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isApproved());
        $this->assertFalse($response->isApproving());
        $this->assertFalse($response->isDeposited());
        $this->assertFalse($response->isDepositing());

        $this->assertEquals(ResponseInterface::INVALID, $response->getStatus());
        $this->assertEquals('50001111', $response->getErrorCode());
        $this->assertEquals('Some of the data entered is incorrect. Please retry.', $response->getErrorDescription());
    }

    /**
     * Test a response for an approving payment
     */
    public function testApproving()
    {
        $response = $this->createResponse('approving');

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isApproved());
        $this->assertTrue($response->isApproving());
        $this->assertFalse($response->isDeposited());
        $this->assertFalse($response->isDepositing());

        $this->assertEquals(ResponseInterface::AUTHORIZATION_WAITING, $response->getStatus());
        $this->assertEmpty($response->getErrorCode());
        $this->assertEmpty($response->getErrorDescription());
        $this->assertNotEmpty($response->getPaymentId());
        $this->assertNotEmpty($response->getAmount());
    }

    /**
     * Test a response for an approving payment
     */
    public function testApproved()
    {
        $response = $this->createResponse('approved');

        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isApproved());
        $this->assertFalse($response->isApproving());
        $this->assertFalse($response->isDeposited());
        $this->assertTrue($response->isDepositing());

        $this->assertEquals(ResponseInterface::AUTHORIZED, $response->getStatus());
        $this->assertEmpty($response->getErrorCode());
    }

    /**
     * Test a response for an approving payment
     */
    public function testDepositing()
    {
        $response = $this->createResponse('depositing');

        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isApproved());
        $this->assertFalse($response->isApproving());
        $this->assertFalse($response->isDeposited());
        $this->assertTrue($response->isDepositing());

        $this->assertEquals(ResponseInterface::PAYMENT_PROCESSING, $response->getStatus());
        $this->assertEmpty($response->getErrorCode());
    }

    /**
     * Test a response for an approving payment
     */
    public function testDeposited()
    {
        $response = $this->createResponse('deposited');

        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isApproved());
        $this->assertFalse($response->isApproving());
        $this->assertTrue($response->isDeposited());
        $this->assertFalse($response->isDepositing());

        $this->assertEquals(ResponseInterface::PAYMENT_REQUESTED, $response->getStatus());
        $this->assertEmpty($response->getErrorCode());
    }

    /**
     * Test a response for an approving payment
     */
    public function testNotManaged()
    {
        $response = $this->createResponse('not_managed');

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isApproved());
        $this->assertFalse($response->isApproving());
        $this->assertFalse($response->isDeposited());
        $this->assertFalse($response->isDepositing());
    }

    /**
     * Test a response for an approving payment
     */
    public function testNotApproved()
    {
        $response = $this->createResponse('not_approved');

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isApproved());
        $this->assertFalse($response->isApproving());
        $this->assertFalse($response->isDeposited());
        $this->assertFalse($response->isDepositing());

        $this->assertNotEmpty($response->getErrorCode());
        $this->assertNotEmpty($response->getErrorDescription());
    }

    /**
     * Test a response for an approving payment
     */
    public function testNotDeposited()
    {
        $response = $this->createResponse('not_deposited');

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isApproved());
        $this->assertFalse($response->isApproving());
        $this->assertFalse($response->isDeposited());
        $this->assertFalse($response->isDepositing());

        $this->assertNotEmpty($response->getErrorCode());
        $this->assertNotEmpty($response->getErrorDescription());
    }

    /**
     * @param string $state
     *
     * @return \ETS\Payment\OgoneBundle\Response\DirectResponse
     */
    protected function createResponse($state)
    {
        $filename = sprintf(__DIR__ . '/../../Resources/fixtures/%s.xml', $state);
        $xml = file_get_contents($filename);

        return new DirectResponse(new \SimpleXMLElement($xml));
    }
}
