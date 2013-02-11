<?php

namespace ETS\Payment\OgoneBundle\Tests\Plugin;

use JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException;
use JMS\Payment\CoreBundle\Plugin\Exception\PaymentPendingException;
use JMS\Payment\CoreBundle\Entity\FinancialTransaction;
use JMS\Payment\CoreBundle\Entity\Payment;
use JMS\Payment\CoreBundle\Entity\ExtendedData;
use JMS\Payment\CoreBundle\Entity\PaymentInstruction;

use ETS\Payment\OgoneBundle\Plugin\OgoneGatewayPluginMock;
use ETS\Payment\OgoneBundle\Tools\ShaIn;
use ETS\Payment\OgoneBundle\Plugin\Configuration\Redirection;
use ETS\Payment\OgoneBundle\Plugin\Configuration\Design;

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
 * Sha-1 In tool
 *
 * @author ETSGlobal <e4-devteam@etsglobal.org>
 */
class OgoneGatewayPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param FinancialTransaction $transaction
     */
    public function testRequestUrls()
    {
        $this->markTestIncomplete();
    }

    /**
     * @param boolean $debug
     */
    public function testNewTransactionRequiresAnAction()
    {
        $plugin = $this->createPluginMock(true);

        $transaction = $this->createTransaction(42, 'EUR');
        $transaction->getExtendedData()->set('lang', 'en_US');

        try {
            $plugin->approveAndDeposit($transaction, 42);

            $this->fail('Plugin was expected to throw an exception.');
        }
        catch (ActionRequiredException $ex) {

            $action = $ex->getAction();

            if (!$action instanceof \JMS\Payment\CoreBundle\Plugin\Exception\Action\VisitUrl) {
                $this->fail('The exception should contain a VisitUrl action.');
            }

            $this->assertRegExp('#https://secure.ogone.com/ncol/test/orderstandard.asp\?AMOUNT=4200&CURRENCY=EUR&LANGUAGE=en_US&ORDERID=.*&SHASIGN=.*#', $action->getUrl());
        }

        return $transaction;
    }

    /**
     * @param FinancialTransaction $transaction
     *
     * @depends testNewTransactionRequiresAnAction
     */
    public function testApprovingTransaction(FinancialTransaction $transaction)
    {
        $plugin = $this->createPluginMock(true, 'approving');
        $this->markTestIncomplete();

        try {
            $plugin->approveAndDeposit($transaction, false);

            $this->fail('Plugin was expected to throw an exception.');
        }
        catch (ActionRequiredException $ex) {
            $this->fail('Plugin was no expected to throw an action exception.');
        }
        catch (\Exception $ex) {

        }
    }

    /**
     * @param FinancialTransaction $transaction
     *
     * @depends testNewTransactionRequiresAnAction
     */
    public function testApprovedTransaction(FinancialTransaction $transaction)
    {
        $this->markTestIncomplete();
    }

    /**
     * @param FinancialTransaction $transaction
     *
     * @depends testNewTransactionRequiresAnAction
     */
    public function testDepositingTransaction(FinancialTransaction $transaction)
    {
        $this->markTestIncomplete();
    }

    /**
     * @param FinancialTransaction $transaction
     *
     * @depends testNewTransactionRequiresAnAction
     */
    public function testDepositedTransaction(FinancialTransaction $transaction)
    {
        $this->markTestIncomplete();
    }

    /**
     * @param FinancialTransaction $transaction
     *
     * @depends testNewTransactionRequiresAnAction
     */
    public function testUnknowStateGenerateAnException(FinancialTransaction $transaction)
    {
        $this->markTestIncomplete();
    }

    /**
     * @param $amount
     * @param $currency
     * @param $data
     *
     * @return \JMS\Payment\CoreBundle\Entity\FinancialTransaction
     */
    protected function createTransaction($amount, $currency)
    {
        $transaction = new FinancialTransaction();
        $transaction->setRequestedAmount($amount);

        $paymentInstruction = new PaymentInstruction($amount, $currency, 'ogone_gateway', new ExtendedData());

        $payment = new Payment($paymentInstruction, $amount);
        $payment->addTransaction($transaction);

        return $transaction;
    }

    /**
     * @return OgoneGatewayPlugin
     */
    protected function createPluginMock($debug = false, $state = '')
    {
        $tokenMock = $this->getMock('ETS\Payment\OgoneBundle\Client\TokenInterface');
        $filename = sprintf(__DIR__ . '/../../Resources/fixtures/%s.xml', $state);

        return new OgoneGatewayPluginMock($tokenMock, new ShaIn($tokenMock), new Redirection(), new Design(), $debug, $filename);
    }
}