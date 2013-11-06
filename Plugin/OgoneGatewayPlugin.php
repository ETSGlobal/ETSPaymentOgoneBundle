<?php

namespace ETS\Payment\OgoneBundle\Plugin;

use JMS\Payment\CoreBundle\Plugin\PluginInterface,
    JMS\Payment\CoreBundle\Plugin\Exception\PaymentPendingException,
    JMS\Payment\CoreBundle\Plugin\Exception\FinancialException,
    JMS\Payment\CoreBundle\Plugin\Exception\CommunicationException,
    JMS\Payment\CoreBundle\BrowserKit\Request,
    JMS\Payment\CoreBundle\Plugin\ErrorBuilder,
    JMS\Payment\CoreBundle\Plugin\Exception\Action\VisitUrl,
    JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException,
    JMS\Payment\CoreBundle\Model\FinancialTransactionInterface,
    JMS\Payment\CoreBundle\Model\PaymentInstructionInterface,
    JMS\Payment\CoreBundle\Plugin\GatewayPlugin;

use ETS\Payment\OgoneBundle\Client\TokenInterface,
    ETS\Payment\OgoneBundle\Direct\Response,
    ETS\Payment\OgoneBundle\Tools\ShaIn;

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
    /**
     * @var TokenInterface
     */
    protected $token;

    /**
     * @var ShaIn
     */
    protected $shaInTool;

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
     * @var array
     */
    public static $additionalData = array(
        "CN"           => 35,
        "EMAIL"        => 50,
        "OWNERZIP"     => 10,
        "OWNERADDRESS" => 35,
        "OWNERCTY"     => 2,
        "OWNERTOWN"    => 40,
        "OWNERTELNO"   => 20,
        "OWNERTELNO2"  => 20,
    );

    /**
     * @param TokenInterface            $token
     * @param ShaIn                     $shaInTool
     * @param Configuration\Redirection $redirectionConfig
     * @param Configuration\Design      $designConfig
     * @param boolean                   $debug
     * @param boolean                   $utf8
     */
    public function __construct(TokenInterface $token, ShaIn $shaInTool, Configuration\Redirection $redirectionConfig, Configuration\Design $designConfig, $debug, $utf8)
    {
        parent::__construct($debug);

        $this->token             = $token;
        $this->shaInTool         = $shaInTool;
        $this->redirectionConfig = $redirectionConfig;
        $this->designConfig      = $designConfig;
        $this->utf8              = $utf8;
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
        if ($transaction->getState() === FinancialTransactionInterface::STATE_NEW) {
            throw $this->createRedirectActionException($transaction);
        }

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
     */
    public function approve(FinancialTransactionInterface $transaction, $retry)
    {
        if ($transaction->getState() === FinancialTransactionInterface::STATE_NEW) {
            throw $this->createRedirectActionException($transaction);
        }

        $response = $this->requestDoDirectRequest($transaction);

        if ($response->isApproving()) {
            throw new PaymentPendingException(sprintf('Payment is still approving, status: %s.', $response->getStatus()));
        }

        if (!$response->isApproved()) {
            $ex = new FinancialException(sprintf('Payment status "%s" is not valid for approvment', $response->getStatus()));
            $ex->setFinancialTransaction($transaction);
            $transaction->setResponseCode($response->getErrorCode());
            $transaction->setReasonCode($response->getErrorDescription());

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
     * @return mixed
     */
    public function deposit(FinancialTransactionInterface $transaction, $retry)
    {
        if ($transaction->getState() === FinancialTransactionInterface::STATE_NEW) {
            throw $this->createRedirectActionException($transaction);
        }

        $response = $this->requestDoDirectRequest($transaction);

        if ($response->isDepositing()) {
            throw new PaymentPendingException(sprintf('Payment is still pending, status: %s.', $response->getStatus()));
        }

        if (!$response->isDeposited()) {
            $ex = new FinancialException(sprintf('Payment status "%s" is not valid for depositing', $response->getStatus()));
            $ex->setFinancialTransaction($transaction);
            $transaction->setResponseCode($response->getErrorCode());
            $transaction->setReasonCode($response->getErrorDescription());

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
     * @throws JMS\Payment\CoreBundle\Plugin\Exception\InvalidPaymentInstructionException if the the PaymentInstruction is not valid
     */
    public function checkPaymentInstruction(PaymentInstructionInterface $paymentInstruction)
    {
        $errorBuilder = new ErrorBuilder();
        $data = $paymentInstruction->getExtendedData();

        if (!$data->has('lang')) {
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
    public function createRedirectActionException(FinancialTransactionInterface $transaction)
    {
        $actionRequest = new ActionRequiredException('User must authorize the transaction');
        $actionRequest->setFinancialTransaction($transaction);

        $instruction = $transaction->getPayment()->getPaymentInstruction();
        $extendedData = $transaction->getExtendedData();
        $extendedData->set('ORDERID', uniqid());

        $additionalData = array();

        foreach (self::getAdditionalDataKeys() as $key) {
            if ($extendedData->has($key)) {
                $additionalData[$key] = $extendedData->get($key);
            }
        }

        $parameters = array_merge(
            self::normalize($additionalData),
            $this->redirectionConfig->getRequestParameters($extendedData),
            $this->designConfig->getRequestParameters($extendedData),
            array(
                "PSPID"    => $this->token->getPspid(),
                "ORDERID"  => $extendedData->get('ORDERID'),
                "AMOUNT"   => $transaction->getRequestedAmount() * 100,
                "CURRENCY" => $instruction->getCurrency(),
                "LANGUAGE" => $extendedData->get('lang')
            )
        );

        $parameters['SHASIGN'] = $this->shaInTool->generate($parameters);

        ksort($parameters);

        $actionRequest->setAction(new VisitUrl($this->getStandardOrderUrl() . '?' . http_build_query($parameters)));

        throw $actionRequest;
    }

    /**
     * Perform direct online payment operations
     *
     * @param FinancialTransactionInterface $transaction
     *
     * @throws FinancialException
     * @return \ETS\Payment\OgoneBundle\Direct\ResponseInterface
     */
    protected function requestDoDirectRequest(FinancialTransactionInterface $transaction)
    {
        $response = $this->sendApiRequest(array(
            'PSPID' => $this->token->getPspid(),
            'USERID' => $this->token->getApiUser(),
            'PSWD' => $this->token->getApiPassword(),
            'ORDERID' => $transaction->getExtendedData()->get('ORDERID'),
        ));

        $transaction->setReferenceNumber($response->getPaymentId());

        if (!$response->isSuccessful()) {
            $transaction->setResponseCode($response->getErrorCode());
            $transaction->setReasonCode($response->getStatus());

            $ex = new FinancialException('Ogone-Response was not successful: '.$response->getErrorDescription());
            $ex->setFinancialTransaction($transaction);

            throw $ex;
        }

        return $response;
    }

    /**
     * Send requests to Ogone API
     *
     * @param array $parameters
     *
     * @throws CommunicationException
     * @return \ETS\Payment\OgoneBundle\Direct\ResponseInterface
     */
    protected function sendApiRequest(array $parameters)
    {
        $request = new Request($this->getDirectQueryUrl(), 'POST', $parameters);
        $response = $this->request($request);

        if (200 !== $response->getStatus()) {
            throw new CommunicationException('The API request was not successful (Status: '.$response->getStatus().'): '.$response->getContent());
        }

        $xml = new \SimpleXMLElement($response->getContent());

        return new Response($xml);
    }

    /**
     * @return string
     */
    protected function getStandardOrderUrl()
    {
        return sprintf(
            'https://secure.ogone.com/ncol/%s/orderstandard%s.asp',
            $this->debug ? 'test' : 'prod',
            $this->utf8 ? '_utf8' : ''
        );
    }

    /**
     * @return string
     */
    protected function getDirectQueryUrl()
    {
        return sprintf(
            'https://secure.ogone.com/ncol/%s/querydirect%s.asp',
            $this->debug ? 'test' : 'prod',
            $this->utf8 ? '_utf8' : ''
        );
    }

    /**
     * Remove all unwanted characters
     * and unset the optional data, if it is too long.
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
    public static function getAdditionalDataKeys()
    {
        return array_keys(self::$additionalData);
    }

    /**
     * @param string $key
     *
     * @return integer
     * @throws \InvalidArgumentException
     */
    public static function getAdditionalDataMaxLength($key)
    {
        if (!isset(self::$additionalData[$key])) {

            throw new \InvalidArgumentException(sprintf(
                'Additional data "%s" not supported. Expected values: %s',
                $key,
                implode(', ', self::getAdditionalDataKeys()
            )));
        }

        return self::$additionalData[$key];
    }
}
