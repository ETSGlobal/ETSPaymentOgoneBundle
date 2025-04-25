<?php

declare(strict_types=1);

namespace ETS\Payment\OgoneBundle\Tests\Plugin;

use ETS\Payment\OgoneBundle\Plugin\OgoneBatchGatewayPluginMock;
use ETS\Payment\OgoneBundle\Service\OgoneFileBuilder;
use JMS\Payment\CoreBundle\Entity\ExtendedData;
use JMS\Payment\CoreBundle\Entity\FinancialTransaction;
use JMS\Payment\CoreBundle\Entity\Payment;
use JMS\Payment\CoreBundle\Entity\PaymentInstruction;
use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException;

use ETS\Payment\OgoneBundle\Plugin\OgoneBatchGatewayPlugin;
use ETS\Payment\OgoneBundle\Test\RequestStubber;
use JMS\Payment\CoreBundle\Plugin\Exception\CommunicationException;
use JMS\Payment\CoreBundle\Plugin\Exception\FinancialException;
use JMS\Payment\CoreBundle\Plugin\Exception\PaymentPendingException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Tests\Logger;

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

class OgoneBatchGatewayPluginTest extends TestCase
{
    private RequestStubber $requestStubber;

    public function setUp(): void
    {
        $this->requestStubber = new RequestStubber([
            ['orderID', null, false, 42],
            ['amount', null, false, '42'],
            ['currency', null, false, 'EUR'],
            ['PM', null, false, 'credit card'],
            ['STATUS', null, false, 5],
            ['CARDNO', null, false, 4567123478941234],
            ['PAYID', null, false, 43],
            ['SHASign', null, false, 'fzgzgzghz4648zh6z5h'],
        ]);
    }

    public function provideTestTestRequestUrls(): array
    {
        return [
            [true, false, 'getStandardOrderUrl', 'https://secure.ogone.com/ncol/test/orderstandard.asp'],
            [false, false, 'getStandardOrderUrl', 'https://secure.ogone.com/ncol/prod/orderstandard.asp'],
            [true, false, 'getDirectQueryUrl', 'https://secure.ogone.com/ncol/test/querydirect.asp'],
            [false, false, 'getDirectQueryUrl', 'https://secure.ogone.com/ncol/prod/querydirect.asp'],
            [true, false, 'getBatchUrl', 'https://secure.ogone.com/ncol/test/AFU_agree.asp'],
            [false, true, 'getBatchUrl', 'https://secure.ogone.com/ncol/prod/AFU_agree.asp'],
        ];
    }

    /**
     * @param boolean $debug    Debug mode
     * @param boolean $utf8     UTF8 mode
     * @param string  $method   Method to test
     * @param string  $expected Expected result
     *
     * @dataProvider provideTestTestRequestUrls
     */
    public function testRequestUrls($debug, $utf8, $method, $expected): void
    {
        $plugin = $this->createPluginMock(null, $debug, $utf8);

        $reflectionMethod = new \ReflectionMethod('ETS\Payment\OgoneBundle\Plugin\OgoneBatchGatewayPlugin', $method);
        $reflectionMethod->setAccessible(true);

        $this->assertEquals($expected, $reflectionMethod->invoke($plugin));
    }

    /**
     * @group refund
     * @return FinancialTransaction
     */
    public function testNewTransactionRequiresAnAction()
    {
        $plugin = $this->createPluginMock();

        $extendedData = [
            'ORDERID' => 1234,
            'PAYID' => 9876,
            'CLIENTID' => 'CLIENT1',
            'CLIENTREF' => 123456,
            'LEGALCOMMITMENT' => 'LEGAL',
            'ALIASID' => 'ALIASID',
            'ARTICLES' => [],
            'TRANSACTIONID' => 4567,
            'ISREFUND' => false
        ];

        $transaction = $this->createTransaction(42, 'EUR', $extendedData);
        $transaction->getExtendedData()->set('lang', 'en_US');

        try {
            $plugin->approveAndDeposit($transaction, 42);
        } catch(\Exception $e) {
            $this->assertTrue($e instanceof ActionRequiredException);
        }

        $transaction->setState(FinancialTransactionInterface::STATE_PENDING);
        $transaction->setReasonCode('action_required');
        $transaction->setResponseCode('pending');

        return $transaction;
    }

    public function testApproveRequiresAnActionForNewTransactions(): void
    {
        $this->expectException(ActionRequiredException::class);
        $this->expectExceptionMessage('Transaction needs to be in state 4');

        $plugin = $this->createPluginMock();
        $extendedData = [
            'ORDERID' => 1234,
            'PAYID' => 9876,
            'CLIENTID' => 'CLIENT1',
            'ALIASID' => 'ALIASID',
            'ARTICLES' => [],
        ];

        $transaction = $this->createTransaction(42, 'EUR', $extendedData);
        $transaction->getExtendedData()->set('lang', 'en_US');

        $plugin->approve($transaction, 42);
    }

    public function testDepositRequiresAnActionForNewTransactions(): void
    {
        $this->expectException(ActionRequiredException::class);
        $this->expectExceptionMessage('Transaction needs to be in state 4');

        $plugin = $this->createPluginMock();
        $extendedData = [
            'ORDERID' => 1234,
            'PAYID' => 9876,
            'CLIENTID' => 'CLIENT1',
            'ALIASID' => 'ALIASID',
            'ARTICLES' => [],
        ];

        $transaction = $this->createTransaction(42, 'EUR', $extendedData);
        $transaction->getExtendedData()->set('lang', 'en_US');

        $plugin->deposit($transaction, 42);
    }

    /** @depends testNewTransactionRequiresAnAction */
    public function testNewRefundingTransaction(FinancialTransaction $transaction): void
    {
        $this->expectException(PaymentPendingException::class);
        $this->expectExceptionMessage('Payment/Refund is still approving/refunding, status: 0');

        $plugin = $this->createPluginMock('new_refund');
        $plugin->approveAndDeposit($transaction, 42);
    }

    /** @depends testNewTransactionRequiresAnAction */
    public function testRefundingTransaction(FinancialTransaction $transaction): void
    {
        $this->expectException(PaymentPendingException::class);
        $this->expectExceptionMessage('Payment/Refund is still approving/refunding, status: 81');

        $plugin = $this->createPluginMock('refunding');
        $plugin->approveAndDeposit($transaction, 42);
    }

    /** @depends testNewTransactionRequiresAnAction */
    public function testRefundedTransaction(FinancialTransaction $transaction): void
    {
        $plugin = $this->createPluginMock('refunded');

        $plugin->approveAndDeposit($transaction, 42);

        $this->assertEquals(42, $transaction->getProcessedAmount());
        $this->assertEquals('success', $transaction->getResponseCode());
        $this->assertEquals('none', $transaction->getReasonCode());
        $this->assertEquals('1111111', $transaction->getReferenceNumber());
    }

    /**
     * @depends testNewTransactionRequiresAnAction
     * @group refund
     */
    public function testRefundWithErrorTransaction(FinancialTransaction $transaction): void
    {
        $this->expectException(FinancialException::class);
        $this->expectExceptionMessage('Ogone-Response was not successful: A technical problem has occurred. Please try again.');

        $transaction->getExtendedData()->set('ISREFUND', true);
        $plugin = $this->createPluginMock('refund_error');
        $plugin->approveAndDeposit($transaction, 42);
    }

    /** @depends testNewTransactionRequiresAnAction */
    public function testApproveAndDepositWhenDeposited(FinancialTransaction $transaction): void
    {
        $plugin = $this->createPluginMock('deposited');

        $plugin->approveAndDeposit($transaction, false);

        $this->assertEquals(42, $transaction->getProcessedAmount());
        $this->assertEquals('success', $transaction->getResponseCode());
        $this->assertEquals('none', $transaction->getReasonCode());
    }

    /** @depends testNewTransactionRequiresAnAction */
    public function testApprovingTransaction(FinancialTransaction $transaction): void
    {
        $this->expectException(PaymentPendingException::class);
        $this->expectExceptionMessage('Payment/Refund is still approving/refunding, status: 51.');

        $plugin = $this->createPluginMock('approving');

        $plugin->approve($transaction, false);
    }

    /** @depends testNewTransactionRequiresAnAction */
    public function testApprovedTransaction(FinancialTransaction $transaction): void
    {
        $plugin = $this->createPluginMock('approved');

        $plugin->approve($transaction, false);

        $this->assertEquals(42, $transaction->getProcessedAmount());
        $this->assertEquals('success', $transaction->getResponseCode());
        $this->assertEquals('none', $transaction->getReasonCode());
    }

    /** @depends testNewTransactionRequiresAnAction */
    public function testDepositingTransaction(FinancialTransaction $transaction): void
    {
        $this->expectException(PaymentPendingException::class);
        $this->expectExceptionMessage('Payment is still pending, status: 91.');

        $plugin = $this->createPluginMock('depositing');

        $plugin->deposit($transaction, false);
    }

    /** @depends testNewTransactionRequiresAnAction */
    public function testDepositedTransaction(FinancialTransaction $transaction): void
    {
        $plugin = $this->createPluginMock('deposited');

        $plugin->deposit($transaction, false);

        $this->assertEquals(42, $transaction->getProcessedAmount());
        $this->assertEquals('success', $transaction->getResponseCode());
        $this->assertEquals('none', $transaction->getReasonCode());
    }

    /** @depends testNewTransactionRequiresAnAction */
    public function testApproveWithUnknowStateGenerateAnException(FinancialTransaction $transaction): void
    {
        $this->expectException(FinancialException::class);
        $this->expectExceptionMessage('Status "74" is not valid for approvment');

        $plugin = $this->createPluginMock('not_managed');

        $plugin->approve($transaction, false);
    }

    /** @depends testNewTransactionRequiresAnAction */
    public function testDepositWithUnknowStateGenerateAnException(FinancialTransaction $transaction): void
    {
        $this->expectException(FinancialException::class);
        $this->expectExceptionMessage('Payment status "74" is not valid for depositing/refunding');

        $plugin = $this->createPluginMock('not_managed');

        $plugin->deposit($transaction, false);
    }

    /** @depends testNewTransactionRequiresAnAction */
    public function testInvalidStateGenerateAnException(FinancialTransaction $transaction): void
    {
        $this->expectException(FinancialException::class);
        $this->expectExceptionMessage('Ogone-Response was not successful: Some of the data entered is incorrect. Please retry.');

        $plugin = $this->createPluginMock('caa_invalid');

        $plugin->deposit($transaction, false);
    }

    /** @depends testNewTransactionRequiresAnAction */
    public function testSendApiRequestFail(FinancialTransaction $transaction): void
    {
        $this->expectException(CommunicationException::class);
        $this->expectExceptionMessage('The API request was not successful (Status: 500):');

        $plugin = $this->createPluginMock('500');

        $plugin->approve($transaction, false);
    }

    public function testProcesses(): void
    {
        $plugin = $this->createPluginMock('not_managed');

        $this->assertTrue($plugin->processes('ogone_caa'));
        $this->assertFalse($plugin->processes('paypal_express_checkout'));
    }

    /**
     * @param string $amount
     * @param string $currency
     */
    protected function createTransaction($amount, $currency, array $extendedDataValues = ['CN' => 'Foo Bar']): FinancialTransaction
    {
        $transaction = new FinancialTransaction();
        $transaction->setRequestedAmount($amount);

        $extendedData = new ExtendedData();
        foreach ($extendedDataValues as $key => $value) {
            $extendedData->set($key, $value);
        }

        $paymentInstruction = new PaymentInstruction($amount, $currency, 'ogone_caa', $extendedData);

        $payment = new Payment($paymentInstruction, $amount);
        $payment->addTransaction($transaction);

        return $transaction;
    }

    /**
     * @param string  $state
     * @param boolean $debug
     *
     * @return OgoneBatchGatewayPlugin
     */
    protected function createPluginMock($state = null, $debug = true)
    {
        $tokenMock  = $this->createMock('ETS\Payment\OgoneBundle\Client\TokenInterface');
        $ogoneFileBuilder = new OgoneFileBuilder($tokenMock);
        $logger = new Logger();
        $pluginMock = new OgoneBatchGatewayPluginMock(
            $tokenMock,
            $ogoneFileBuilder,
            $logger,
            $debug
        );

        if (null !== $state) {
            $pluginMock->setFilename($state);
        }

        return $pluginMock;
    }
}
