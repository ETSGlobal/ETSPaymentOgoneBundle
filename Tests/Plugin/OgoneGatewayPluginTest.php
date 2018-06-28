<?php

namespace ETS\Payment\OgoneBundle\Tests\Plugin;

use ETS\Payment\OgoneBundle\Client\Token;
use ETS\Payment\OgoneBundle\Hash\Sha1In;
use ETS\Payment\OgoneBundle\Plugin\Configuration\Design;
use ETS\Payment\OgoneBundle\Plugin\Configuration\Redirection;
use ETS\Payment\OgoneBundle\Plugin\OgoneGatewayPlugin;
use ETS\Payment\OgoneBundle\Plugin\OgoneGatewayPluginMock;
use ETS\Payment\OgoneBundle\Response\FeedbackResponse;
use ETS\Payment\OgoneBundle\Test\RequestStubber;
use JMS\Payment\CoreBundle\Entity\ExtendedData;
use JMS\Payment\CoreBundle\Entity\FinancialTransaction;
use JMS\Payment\CoreBundle\Entity\Payment;
use JMS\Payment\CoreBundle\Entity\PaymentInstruction;
use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use JMS\Payment\CoreBundle\Plugin\Exception\Action\VisitUrl;
use JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException;
use PHPUnit\Framework\TestCase;

/**
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
 * OgoneGatewayPlugin tests
 */
class OgoneGatewayPluginTest extends TestCase
{
    /**
     * @var \ETS\Payment\OgoneBundle\Test\RequestStubber
     */
    private $requestStubber;

    public function setUp()
    {
        $this->requestStubber = new RequestStubber(
            [
                ['orderID', null, false, 42],
                ['amount', null, false, '42'],
                ['currency', null, false, 'EUR'],
                ['PM', null, false, 'credit card'],
                ['STATUS', null, false, 5],
                ['CARDNO', null, false, 4567123478941234],
                ['PAYID', null, false, 43],
                ['SHASign', null, false, 'fzgzgzghz4648zh6z5h']
            ]
        );
    }

    /**
     * @return array
     */
    public function provideTestTestRequestUrls(): array
    {
        return [
            [true, false, 'getStandardOrderUrl', 'https://secure.ogone.com/ncol/test/orderstandard.asp'],
            [false, false, 'getStandardOrderUrl', 'https://secure.ogone.com/ncol/prod/orderstandard.asp'],
            [false, true, 'getStandardOrderUrl', 'https://secure.ogone.com/ncol/prod/orderstandard_utf8.asp'],
            [true, false, 'getDirectQueryUrl', 'https://secure.ogone.com/ncol/test/querydirect.asp'],
            [false, false, 'getDirectQueryUrl', 'https://secure.ogone.com/ncol/prod/querydirect.asp'],
            [false, true, 'getDirectQueryUrl', 'https://secure.ogone.com/ncol/prod/querydirect_utf8.asp'],
        ];
    }

    /**
     * @param boolean $debug    Debug mode
     * @param boolean $utf8     UTF8 mode
     * @param string  $method   Method to test
     * @param string  $expected Expected result
     *
     * @dataProvider provideTestTestRequestUrls
     *
     * @throws \ReflectionException
     */
    public function testRequestUrls($debug, $utf8, $method, $expected)
    {
        $plugin = $this->createPluginMock('', $debug, $utf8);

        $reflectionMethod = new \ReflectionMethod(OgoneGatewayPlugin::class, $method);
        $reflectionMethod->setAccessible(true);

        $this->assertEquals($expected, $reflectionMethod->invoke($plugin));
    }

    public function testNewTransactionRequiresAnAction()
    {
        $plugin = $this->createPluginMock();

        $transaction = $this->createTransaction(42, 'EUR');
        $transaction->getExtendedData()->set('lang', 'en_US');

        try {
            $plugin->approveAndDeposit($transaction, true);

            $this->fail('Plugin was expected to throw an exception.');
        } catch (ActionRequiredException $ex) {
            $action = $ex->getAction();

            if (!$action instanceof VisitUrl) {
                $this->fail("The exception's action should be of type 'VisitUrl'.");
            }

            $this->assertRegExp('#https://secure.ogone.com/ncol/test/orderstandard.asp\?AMOUNT=4200&CN=Foo\+Bar&CURRENCY=EUR&LANGUAGE=en_US&ORDERID=.*&SHASIGN=.*#', $action->getUrl());
        }

        $transaction->setState(FinancialTransactionInterface::STATE_PENDING);
        $transaction->setReasonCode('action_required');
        $transaction->setResponseCode('pending');

        return $transaction;
    }

    /**
     * @expectedException        \JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException
     * @expectedExceptionMessage User must authorize the transaction
     */
    public function testApproveRequiresAnActionForNewTransactions()
    {
        $plugin = $this->createPluginMock();

        $transaction = $this->createTransaction(42, 'EUR');
        $transaction->getExtendedData()->set('lang', 'en_US');

        $plugin->approve($transaction, true);
    }

    /**
     * @expectedException        \JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException
     * @expectedExceptionMessage User must authorize the transaction
     */
    public function testDepositRequiresAnActionForNewTransactions()
    {
        $plugin = $this->createPluginMock();

        $transaction = $this->createTransaction(42, 'EUR');
        $transaction->getExtendedData()->set('lang', 'en_US');

        $plugin->deposit($transaction, true);
    }

    /**
     * @param FinancialTransaction $transaction
     *
     * @depends testNewTransactionRequiresAnAction
     */
    public function testApproveAndDepositWhenDeposited(FinancialTransaction $transaction)
    {
        $plugin = $this->createPluginMock('deposited');

        $plugin->approveAndDeposit($transaction, false);

        $this->assertEquals(42, $transaction->getProcessedAmount());
        $this->assertEquals('success', $transaction->getResponseCode());
        $this->assertEquals('none', $transaction->getReasonCode());
    }

    /**
     * @param FinancialTransaction $transaction
     *
     * @depends testNewTransactionRequiresAnAction
     * @expectedException        \JMS\Payment\CoreBundle\Plugin\Exception\PaymentPendingException
     * @expectedExceptionMessage Payment is still approving, status: 51.
     */
    public function testApprovingTransaction(FinancialTransaction $transaction)
    {
        $plugin = $this->createPluginMock('approving');

        $plugin->approve($transaction, false);
    }

    /**
     * @param FinancialTransaction $transaction
     *
     * @depends testNewTransactionRequiresAnAction
     */
    public function testApprovedTransaction(FinancialTransaction $transaction)
    {
        $plugin = $this->createPluginMock('approved');

        $plugin->approve($transaction, false);

        $this->assertEquals(42, $transaction->getProcessedAmount());
        $this->assertEquals('success', $transaction->getResponseCode());
        $this->assertEquals('none', $transaction->getReasonCode());
    }

    /**
     * @param FinancialTransaction $transaction
     *
     * @depends testNewTransactionRequiresAnAction
     * @expectedException        \JMS\Payment\CoreBundle\Plugin\Exception\PaymentPendingException
     * @expectedExceptionMessage Payment is still pending, status: 91.
     */
    public function testDepositingTransaction(FinancialTransaction $transaction)
    {
        $plugin = $this->createPluginMock('depositing');

        $plugin->deposit($transaction, false);
    }

    /**
     * @param FinancialTransaction $transaction
     *
     * @depends testNewTransactionRequiresAnAction
     */
    public function testDepositedTransaction(FinancialTransaction $transaction)
    {
        $plugin = $this->createPluginMock('deposited');

        $plugin->deposit($transaction, false);

        $this->assertEquals(42, $transaction->getProcessedAmount());
        $this->assertEquals('success', $transaction->getResponseCode());
        $this->assertEquals('none', $transaction->getReasonCode());
    }

    /**
     * @param FinancialTransaction $transaction
     *
     * @depends testNewTransactionRequiresAnAction
     * @expectedException        \JMS\Payment\CoreBundle\Plugin\Exception\FinancialException
     * @expectedExceptionMessage Payment status "74" is not valid for approvment
     */
    public function testApproveWithUnknowStateGenerateAnException(FinancialTransaction $transaction)
    {
        $plugin = $this->createPluginMock('not_managed');

        $plugin->approve($transaction, false);
    }

    /**
     * @param FinancialTransaction $transaction
     *
     * @depends testNewTransactionRequiresAnAction
     * @expectedException        \JMS\Payment\CoreBundle\Plugin\Exception\FinancialException
     * @expectedExceptionMessage Payment status "74" is not valid for depositing
     */
    public function testDepositWithUnknowStateGenerateAnException(FinancialTransaction $transaction)
    {
        $plugin = $this->createPluginMock('not_managed');

        $plugin->deposit($transaction, false);
    }

    /**
     * @param FinancialTransaction $transaction
     *
     * @depends testNewTransactionRequiresAnAction
     * @expectedException        \JMS\Payment\CoreBundle\Plugin\Exception\FinancialException
     * @expectedExceptionMessage Ogone-Response was not successful: Some of the data entered is incorrect. Please retry.
     */
    public function testInvalidStateGenerateAnException(FinancialTransaction $transaction)
    {
        $plugin = $this->createPluginMock('invalid');

        $plugin->deposit($transaction, false);
    }

    /**
     * @param FinancialTransaction $transaction
     *
     * @depends testNewTransactionRequiresAnAction
     * @expectedException        \JMS\Payment\CoreBundle\Plugin\Exception\CommunicationException
     * @expectedExceptionMessage The API request was not successful (Status: 500):
     */
    public function testSendApiRequestFail(FinancialTransaction $transaction)
    {
        $plugin = $this->createPluginMock('500');

        $plugin->approve($transaction, false);
    }

    /**
     * @expectedException        \JMS\Payment\CoreBundle\Plugin\Exception\FinancialException
     * @expectedExceptionMessage The payment instruction is invalid.
     */
    public function testInvalidCheckPaymentInstruction()
    {
        $plugin = $this->createPluginMock('not_managed');
        $transaction = $this->createTransaction(42, 'EUR');

        $plugin->checkPaymentInstruction($transaction->getPayment()->getPaymentInstruction());
    }

    /**
     * Test the Check payment instruction with valid datas
     */
    public function testValidCheckPaymentInstruction()
    {
        $plugin = $this->createPluginMock('not_managed');
        $transaction = $this->createTransaction(42, 'EUR');

        $transaction->getExtendedData()->set('lang', 'en_US');

        try {
            $plugin->checkPaymentInstruction($transaction->getPayment()->getPaymentInstruction());
        } catch (\Exception $ex) {
            $this->fail('Exception should not be throw here.');
        }

        $this->assertNotEmpty($transaction->getPayment());
    }

    /**
     * Test the processes function
     */
    public function testProcesses()
    {
        $plugin = $this->createPluginMock('not_managed');

        $this->assertTrue($plugin->processes('ogone_gateway'));
        $this->assertFalse($plugin->processes('paypal_express_checkout'));
    }

    /**
     * @param FinancialTransaction $transaction
     *
     * @depends testNewTransactionRequiresAnAction
     */
    public function testGetResponseReturnsFeedbackResponse(FinancialTransaction $transaction)
    {
        $plugin = $this->createPluginMock();
        $plugin->setFeedbackResponse(new FeedbackResponse($this->requestStubber->getStubbedRequest()));

        $class = new \ReflectionClass($plugin);
        $getResponseMethod = $class->getMethod('getResponse');
        $getResponseMethod->setAccessible(true);

        $response = $getResponseMethod->invokeArgs($plugin, array($transaction));

        $this->assertInstanceOf(FeedbackResponse::class, $response);
    }

    /**
     * @param $amount
     * @param string $currency
     * @param array  $extendedDataValues
     *
     * @return \JMS\Payment\CoreBundle\Entity\FinancialTransaction
     */
    protected function createTransaction($amount, $currency, array $extendedDataValues = ['CN' => 'Foo Bar'])
    {
        $transaction = new FinancialTransaction();
        $transaction->setRequestedAmount($amount);

        $extendedData = new ExtendedData();
        foreach ($extendedDataValues as $key => $value) {
            $extendedData->set($key, $value);
        }

        $paymentInstruction = new PaymentInstruction($amount, $currency, 'ogone_gateway', $extendedData);

        $payment = new Payment($paymentInstruction, $amount);
        $payment->addTransaction($transaction);

        return $transaction;
    }

    /**
     * @param string  $state
     * @param boolean $debug
     * @param boolean $utf8
     *
     * @return OgoneGatewayPlugin
     */
    protected function createPluginMock($state = '', $debug = true, $utf8 = false): OgoneGatewayPlugin
    {
        $tokenMock  = new Token('', '', '', '', '');
        $pluginMock = new OgoneGatewayPluginMock(
            $tokenMock,
            new Sha1In($tokenMock),
            new Redirection(),
            new Design(),
            $debug,
            $utf8
        );

        if ($state) {
            $pluginMock->setFilename($state);
        }

        return $pluginMock;
    }

    /**
     * @return array
     */
    public function provideAdditionalData(): array
    {
        return [
            [
                ['EMAIL' => 'aa.bb@test.com', 'OWNERCTY' => 'city'],
                ['EMAIL' => 'aa.bb@test.com'],
            ],
            [
                ['EMAIL' => 'aa.bb@test.com', 'OWNERADDRESS' => 'main street'],
                ['EMAIL' => 'aa.bb@test.com', 'OWNERADDRESS' => 'main street'],
            ],
        ];
    }

    /**
     * @param array $additionalData
     * @param array $exected
     *
     * @dataProvider provideAdditionalData
     */
    public function testNormalize(array $additionalData, array $exected)
    {
       $this->assertSame($exected, OgoneGatewayPlugin::normalize($additionalData));
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Additional data "foo" not supported. Expected values: PM, BRAND, CN, EMAIL, OWNERZIP, OWNERADDRESS, OWNERCTY, OWNERTOWN, OWNERTELNO, OWNERTELNO2
     */
    public function testNormalizeException()
    {
        OgoneGatewayPlugin::normalize(['foo' => 'bar']);
    }
}
