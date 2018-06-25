<?php

namespace ETS\Payment\OgoneBundle\Plugin;

use JMS\Payment\CoreBundle\BrowserKit\Request;
use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use JMS\Payment\CoreBundle\Model\PaymentInstructionInterface;
use JMS\Payment\CoreBundle\Plugin\ErrorBuilder;
use JMS\Payment\CoreBundle\Plugin\Exception\Action\VisitUrl;
use JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException;
use JMS\Payment\CoreBundle\Plugin\Exception\CommunicationException;
use JMS\Payment\CoreBundle\Plugin\Exception\FinancialException;
use JMS\Payment\CoreBundle\Plugin\Exception\PaymentPendingException;
use JMS\Payment\CoreBundle\Plugin\PluginInterface;

use ETS\Payment\OgoneBundle\Client\TokenInterface;
use ETS\Payment\OgoneBundle\Hash\GeneratorInterface;
use ETS\Payment\OgoneBundle\Response\DirectResponse;
use ETS\Payment\OgoneBundle\Response\ResponseInterface;

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
 * Ogone gateway plugin
 *
 * @author ETSGlobal <ecs@etsglobal.org>
 */
class OgoneGatewayPlugin extends OgoneGatewayBasePlugin
{
    /**
     * @var TokenInterface
     */
    protected $token;

    /**
     * @var GeneratorInterface
     */
    protected $hashGenerator;

    /**
     * @var Configuration\Redirection
     */
    protected $redirectionConfig;

    /**
     * @var Configuration\Design
     */
    protected $designConfig;

    /**
     * @var boolean
     */
    protected $utf8;

    /**
     * @var ResponseInterface
     */
    protected $feedbackResponse;

    /**
     * @var array
     */
    public static $additionalData = array(
        'PM' => 25,
        'BRAND' => 25,
        'CN' => 35,
        'EMAIL' => 50,
        'OWNERZIP' => 10,
        'OWNERADDRESS' => 35,
        'OWNERCTY' => 2,
        'OWNERTOWN' => 40,
        'OWNERTELNO' => 20,
        'OWNERTELNO2' => 20,
    );

    /**
     * @param TokenInterface            $token
     * @param GeneratorInterface        $hashGenerator
     * @param Configuration\Redirection $redirectionConfig
     * @param Configuration\Design      $designConfig
     * @param boolean                   $debug
     * @param boolean                   $utf8
     */
    public function __construct(TokenInterface $token, GeneratorInterface $hashGenerator, Configuration\Redirection $redirectionConfig, Configuration\Design $designConfig, $debug, $utf8)
    {
        parent::__construct($debug, $utf8);

        $this->token             = $token;
        $this->hashGenerator     = $hashGenerator;
        $this->redirectionConfig = $redirectionConfig;
        $this->designConfig      = $designConfig;
        $this->utf8              = $utf8;
    }

    /**
     * @param ResponseInterface $feedbackResponse
     */
    public function setFeedbackResponse(ResponseInterface $feedbackResponse)
    {
        $this->feedbackResponse = $feedbackResponse;
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
        $this->approve($transaction, $retry);
        $this->deposit($transaction, $retry);
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
     *
     * @throws ActionRequiredException If the transaction's state is NEW
     * @throws FinancialException      If payment is not approved
     * @throws PaymentPendingException If payment is still approving
     */
    public function approve(FinancialTransactionInterface $transaction, $retry)
    {
        if ($transaction->getState() === FinancialTransactionInterface::STATE_NEW) {
            throw $this->createRedirectActionException($transaction);
        }

        $response = $this->getResponse($transaction);

        if ($response->isApproving()) {
            throw new PaymentPendingException(sprintf('Payment is still approving, status: %d.', $response->getStatus()));
        }

        if (!$response->isApproved()) {
            $ex = new FinancialException(sprintf('Payment status "%d" is not valid for approvment', $response->getStatus()));
            $ex->setFinancialTransaction($transaction);
            $transaction->setResponseCode($response->getErrorCode());
            $transaction->setReasonCode($response->getStatus());

            throw $ex;
        }

        $transaction->setProcessedAmount($response->getAmount());
        $transaction->setResponseCode(PluginInterface::RESPONSE_CODE_SUCCESS);
        $transaction->setReasonCode(PluginInterface::REASON_CODE_SUCCESS);
    }

    /**
     * This method executes a deposit transaction (aka capture transaction).
     *
     * This method requires that the Payment has already been approved in
     * a prior transaction.
     *
     * A typical use case are Credit Card payments.
     *
     * @param FinancialTransactionInterface $transaction The transaction
     * @param boolean                       $retry       Retry
     *
     * @return void
     *
     * @throws ActionRequiredException If the transaction's state is NEW
     * @throws FinancialException      If payment is not approved
     * @throws PaymentPendingException If payment is still approving
     */
    public function deposit(FinancialTransactionInterface $transaction, $retry): void
    {
        if ($transaction->getState() === FinancialTransactionInterface::STATE_NEW) {
            throw $this->createRedirectActionException($transaction);
        }

        $response = $this->getResponse($transaction);

        if ($response->isDepositing()) {
            throw new PaymentPendingException(sprintf('Payment is still pending, status: %d.', $response->getStatus()));
        }

        if (!$response->isDeposited()) {
            $ex = new FinancialException(sprintf('Payment status "%d" is not valid for depositing', $response->getStatus()));
            $ex->setFinancialTransaction($transaction);
            $transaction->setResponseCode($response->getErrorCode());
            $transaction->setReasonCode($response->getStatus());

            throw $ex;
        }

        $transaction->setProcessedAmount($response->getAmount());
        $transaction->setResponseCode(PluginInterface::RESPONSE_CODE_SUCCESS);
        $transaction->setReasonCode(PluginInterface::REASON_CODE_SUCCESS);
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
     * @throws \JMS\Payment\CoreBundle\Plugin\Exception\InvalidPaymentInstructionException if the the PaymentInstruction is not valid
     */
    public function checkPaymentInstruction(PaymentInstructionInterface $paymentInstruction)
    {
        $errorBuilder = new ErrorBuilder();

        if (!$paymentInstruction->getExtendedData()->has('lang')) {
            $errorBuilder->addDataError('lang', 'form.error.required');
        }

        if ($errorBuilder->hasErrors()) {
            throw $errorBuilder->getException();
        }
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
     *
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
     *
     * @return ActionRequiredException
     *
     * @throws FinancialException
     */
    public function createRedirectActionException(FinancialTransactionInterface $transaction)
    {
        $actionRequestException = new ActionRequiredException('User must authorize the transaction');
        $actionRequestException->setFinancialTransaction($transaction);

        $extendedData = $transaction->getExtendedData();
        if (null === $extendedData || null === $transaction->getPayment()) {
            $ex = new FinancialException('Transaction does not have extended data or payment');
            $ex->setFinancialTransaction($transaction);

            throw $ex;
        }

        if (!$extendedData->has('ORDERID')) {
            $extendedData->set('ORDERID', uniqid('ORDERID', true));
        }

        $additionalData = [];
        foreach (self::getAdditionalDataKeys() as $key) {
            if ($extendedData->has($key)) {
                $additionalData[$key] = $extendedData->get($key);
            }
        }

        $parameters = array_merge(
            self::normalize($additionalData),
            $this->redirectionConfig->getRequestParameters($extendedData),
            $this->designConfig->getRequestParameters($extendedData),
            [
                'ORDERID' => $extendedData->get('ORDERID'),
                'PSPID' => $this->token->getPspid(),
                'AMOUNT' => $transaction->getRequestedAmount() * 100,
                'CURRENCY' => $transaction->getPayment()->getPaymentInstruction()->getCurrency(),
                'LANGUAGE' => $extendedData->get('lang')
            ]
        );

        $parameters['SHASIGN'] = $this->hashGenerator->generate($parameters);

        ksort($parameters, SORT_STRING);

        $actionRequestException->setAction(new VisitUrl($this->getStandardOrderUrl() . '?' . http_build_query($parameters)));

        return $actionRequestException;
    }

    /**
     * Get a Response object from the transaction's extended data if feedback has been provided through a callback,
     * or from a call to ogone's api.
     *
     * @param  FinancialTransactionInterface $transaction
     * @param  boolean                       $forceDirect
     *
     * @return \ETS\Payment\OgoneBundle\Response\ResponseInterface
     *
     * @throws FinancialException
     */
    protected function getResponse(FinancialTransactionInterface $transaction, $forceDirect = false)
    {
        $response = (isset($this->feedbackResponse) && (false === $forceDirect))
                  ? $this->feedbackResponse
                  : $this->getDirectResponse($transaction);

        if (!$response->isSuccessful()) {
            $transaction->setResponseCode($response->getErrorCode());
            $transaction->setReasonCode($response->getStatus());

            $ex = new FinancialException('Ogone-Response was not successful: '.$response->getErrorDescription());
            $ex->setFinancialTransaction($transaction);

            throw $ex;
        }

        $transaction->setReferenceNumber($response->getPaymentId());

        return $response;
    }

    /**
     * Perform direct online payment operations
     *
     * @param FinancialTransactionInterface $transaction
     *
     * @return DirectResponse
     *
     * @throws CommunicationException
     */
    protected function getDirectResponse(FinancialTransactionInterface $transaction): DirectResponse
    {
        $apiData = array(
            'PSPID'   => $this->token->getPspid(),
            'USERID'  => $this->token->getApiUser(),
            'PSWD'    => $this->token->getApiPassword(),
            'ORDERID' => $transaction->getExtendedData()->get('ORDERID'),
        );

        if ($transaction->getExtendedData()->has('PAYID')) {
            $apiData['PAYID'] = $transaction->getExtendedData()->get('PAYID');
        }

        return new DirectResponse($this->sendApiRequest($apiData));
    }

    /**
     * Send requests to Ogone API
     *
     * @param array $parameters
     *
     * @return \SimpleXMLElement
     *
     * @throws CommunicationException
     */
    protected function sendApiRequest(array $parameters)
    {
        $response = $this->request(new Request($this->getDirectQueryUrl(), 'POST', $parameters));

        if (200 !== $response->getStatus()) {
            throw new CommunicationException(sprintf('The API request was not successful (Status: %d): %s', $response->getStatus(), $response->getContent()));
        }

        return new \SimpleXMLElement($response->getContent());
    }

    /**
     * Remove all unwanted characters and unset the optional data, if it is too long.
     *
     * @param array $additionalData
     *
     * @return array
     */
    public static function normalize(array $additionalData)
    {
        foreach ($additionalData as $key => $value) {
            $additionalData[$key] = preg_replace('/\pM*/u', '', normalizer_normalize($value, \Normalizer::FORM_D));

            if (strlen($additionalData[$key]) > self::getAdditionalDataMaxLength($key)) {
                unset($additionalData[$key]);
            }
        }

        return $additionalData;
    }

    /**
     * @return array
     */
    protected static function getAdditionalDataKeys()
    {
        return array_keys(self::$additionalData);
    }

    /**
     * @param string $key
     *
     * @return integer
     *
     * @throws \InvalidArgumentException
     */
    protected static function getAdditionalDataMaxLength($key): int
    {
        if (!isset(self::$additionalData[$key])) {
            throw new \InvalidArgumentException(sprintf(
                'Additional data "%s" not supported. Expected values: %s',
                $key,
                implode(', ', self::getAdditionalDataKeys())
            ));
        }

        return self::$additionalData[$key];
    }

    /**
     * Return direct response content
     *
     * @param FinancialTransactionInterface $transaction
     *
     * @return DirectResponse
     *
     * @throws FinancialException
     * @throws CommunicationException
     */
    public function getDirectResponseContent(FinancialTransactionInterface $transaction): DirectResponse
    {
        $response = $this->getDirectResponse($transaction);

        if (!$response->isSuccessful()) {

            $ex = new FinancialException('Direct Ogone-Response was not successful: ' . $response->getErrorDescription());
            $ex->setFinancialTransaction($transaction);

            throw $ex;
        }

        return $response;
    }
}
