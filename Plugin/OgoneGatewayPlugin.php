<?php

namespace ETS\Payment\OgoneBundle\Plugin;

use JMS\Payment\CoreBundle\Plugin\Exception\Action\VisitUrl,
    JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException,
    JMS\Payment\CoreBundle\Model\FinancialTransactionInterface,
    JMS\Payment\CoreBundle\Model\PaymentInstructionInterface,
    JMS\Payment\CoreBundle\Plugin\GatewayPlugin;

use ETS\Payment\OgoneBundle\Client\TokenInterface;

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
 * Ogone gateway plugin
 *
 * @author ETSGlobal <e4-devteam@etsglobal.org>
 */
class OgoneGatewayPlugin extends GatewayPlugin
{
    protected $ogoneUrl;
    protected $token;

    /**
     * @param TokenInterface $token
     * @param string $ogoneUrl
     */
    public function __construct(TokenInterface $token, $ogoneUrl)
    {
        $this->token    = $token;
        $this->ogoneUrl = $ogoneUrl;
    }

    /**
     * This method executes an approve transaction.
     *
     * By an approval, funds are reserved but no actual money is transferred. A
     * subsequent deposit transaction must be performed to actually transfer the
     * money.
     *
     * A typical use case, would be Credit Card payments where funds are first
     * authorized.
     *
     * @param FinancialTransactionInterface $transaction The transaction
     * @param boolean                       $retry       Whether this is a retry transaction
     */
    public function approve(FinancialTransactionInterface $transaction, $retry)
    {
        $this->createCheckoutBillingAgreement($transaction, 'Authorization');
    }

    /**
     * This method executes a deposit transaction without prior approval
     * (aka "sale", or "authorization with capture" transaction).
     *
     * A typical use case for this method is an electronic check payments
     * where authorization is not supported. It can also be used to deposit
     * money in only one transaction, and thus saving processing fees for
     * another transaction.
     *
     * @param FinancialTransactionInterface $transaction The transaction
     * @param boolean                       $retry       Whether this is a retry transaction
     */
     public function approveAndDeposit(FinancialTransactionInterface $transaction, $retry)
     {
        $this->createCheckoutBillingAgreement($transaction, 'Sale');
     }

    /**
     * This method checks whether all required parameters exist in the given
     * PaymentInstruction, and whether they are syntactically correct.
     *
     * This method is meant to perform a fast parameter validation; no connection
     * to any payment back-end system should be made at this stage.
     *
     * In case, this method is not implemented. The PaymentInstruction will
     * be considered to be valid.
     *
     * @param PaymentInstructionInterface $paymentInstruction
     *
     * @throws JMS\Payment\CoreBundle\Plugin\Exception\InvalidPaymentInstructionException if the the PaymentInstruction is not valid
     */
    public function checkPaymentInstruction(PaymentInstructionInterface $paymentInstruction)
    {
    }

    /**
     * This method validates the correctness, and existence of any account
     * associated with the PaymentInstruction object.
     *
     * This method performs a more thorough validation than checkPaymentInstruction,
     * in that it may actually connect to the payment backend system; no funds should
     * be transferred, though.
     *
     * @throws JMS\Payment\CoreBundle\Plugin\Exception\InvalidPaymentInstructionException if the PaymentInstruction is not valid
     * @param  PaymentInstructionInterface                                                $paymentInstruction
     */
    public function validatePaymentInstruction(PaymentInstructionInterface $paymentInstruction)
    {
    }

    /**
     * Whether this plugin can process payments for the given payment system.
     *
     * A plugin may support multiple payment systems. In these cases, the requested
     * payment system for a specific transaction  can be determined by looking at
     * the PaymentInstruction which will always be accessible either directly, or
     * indirectly.
     *
     * @param  string  $paymentSystemName
     * @return boolean
     */
    public function processes($paymentSystemName)
    {
        return 'ogone_gateway' === $paymentSystemName;
    }

    /**
     * approve Authorized & Sale transactions
     *
     * @param  FinancialTransactionInterface $transaction
     * @param  string                        $paymentAction
     *
     * @throws ActionRequiredException
     */
    public function createCheckoutBillingAgreement(FinancialTransactionInterface $transaction, $paymentAction)
    {
        $actionRequest = new ActionRequiredException('User must authorize the transaction');
        $actionRequest->setFinancialTransaction($transaction);
        $actionRequest->setAction(new VisitUrl($this->ogoneUrl));

        throw $actionRequest;
    }

    /**
     * Perform direct online payment operations
     *
     * @param array $parameters
     */
    public function requestDoDirectPayment(array $parameters)
    {
       $this->sendApiRequest($parameters);
    }

    /**
     * Send requests to Ogone API
     *
     * @param array $parameters
     *
     * @throws CommunicationException
     */
    public function sendApiRequest(array $parameters)
    {
        $request = new Request(
            $this->ogoneUrl,
            'POST',
            $parameters
        );

        $response = $this->request($request);

        if (200 !== $response->getStatus()) {
            throw new CommunicationException('The API request was not successful (Status: '.$response->getStatus().'): '.$response->getContent());
        }
    }

    /**
     * Generate ShaIn String using payment parameters
     *
     * @param array $parameters
     */
    public function getShaInString(array $parameters)
    {
        $shainString ='';
        krsort($parameters);

        foreach ($parameters as $key => $parameter) {
            $shainString = $shainString.$key.'='.$parameter.$this->token->getShain();
        }

        return sha1($shainString);
    }

    /**
     * Get payment info (used to fill ogone request form)
     *
     * @param FinancialTransactionInterface $transaction
     */
    public function getPaymentFormArray(FinancialTransactionInterface $transaction)
    {
        $paymentInfo = array();

        $paymentInfo['PSPID']    = $this->token->getPspid();
        $paymentInfo['AMOUNT']   = $transaction->getRequestedAmount();
        $paymentInfo['CURRENCY'] = $transaction->getPayment()->getPaymentInstruction()->getCurrency();
        $paymentInfo['SHASIGN']  = $this->getShaInString($paymentInfo);

        return $paymentInfo;
    }
}
