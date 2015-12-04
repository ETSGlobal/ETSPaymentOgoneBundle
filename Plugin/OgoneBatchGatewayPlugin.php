<?php

namespace ETS\Payment\OgoneBundle\Plugin;

use ETS\Payment\OgoneBundle\Service\OgoneFileBuilder;
use JMS\Payment\CoreBundle\BrowserKit\Request;
use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use JMS\Payment\CoreBundle\Model\PaymentInstructionInterface;
use JMS\Payment\CoreBundle\Model\ExtendedDataInterface;
use JMS\Payment\CoreBundle\Plugin\ErrorBuilder;
use JMS\Payment\CoreBundle\Plugin\Exception\Action\VisitUrl;
use JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException;
use JMS\Payment\CoreBundle\Plugin\Exception\CommunicationException;
use JMS\Payment\CoreBundle\Plugin\Exception\FinancialException;
use JMS\Payment\CoreBundle\Plugin\Exception\InvalidPaymentInstructionException;
use JMS\Payment\CoreBundle\Plugin\Exception\PaymentPendingException;
use JMS\Payment\CoreBundle\Plugin\PluginInterface;

use ETS\Payment\OgoneBundle\Client\TokenInterface;
use ETS\Payment\OgoneBundle\Hash\GeneratorInterface;
use ETS\Payment\OgoneBundle\Response\DirectResponse;
use ETS\Payment\OgoneBundle\Response\ResponseInterface;
use Psr\Log\LoggerInterface;

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
class OgoneBatchGatewayPlugin extends OgoneGatewayBasePlugin
{

    const TRANSACTION_CODE_NEW         = 'ATR'; //code for new orders (transactions)
    const TRANSACTION_CODE_MAINTENANCE = 'MTR'; //code for maintenance operations on existing transactions
    const AUTHORIZATION                = 'RES';
    const PAYMENT                      = 'SAS';
    const PARTIAL_REFUND               = 'RFD'; //means others operations can be done on the same transaction

    /**
     * @var TokenInterface
     */
    protected $token;
    protected $ogoneFileBuilder;
    protected $logger;

    /**
     * @return array
     */
    public static function getAvailableOperations()
    {
        return [
            'AUTHORIZATION' => OgoneBatchGatewayPlugin::AUTHORIZATION,
            'PAYMENT' => OgoneBatchGatewayPlugin::PAYMENT,
            'PARTIAL_REFUND' => OgoneBatchGatewayPlugin::PARTIAL_REFUND,
        ];
    }


    /**
     * @param TokenInterface            $token
     * @param OgoneFileBuilder          $ogoneFileBuilder
     * @param boolean                   $debug
     */
    public function __construct(TokenInterface $token, OgoneFileBuilder $ogoneFileBuilder, LoggerInterface $logger, $debug)
    {
        parent::__construct($debug);

        $this->token = $token;
        $this->ogoneFileBuilder = $ogoneFileBuilder;
        $this->logger = $logger;
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
        $this->logger->debug('approving...');
        if ($transaction->getState() === FinancialTransactionInterface::STATE_NEW) {
            throw new ActionRequiredException('Transaction needs to be in state 4');
        }

        if (!isset($this->feedbackResponse)) {
            $params  = array(
                'ORDERID' => $transaction->getExtendedData()->get('ORDERID'),
                'PAYID' => $transaction->getExtendedData()->get('PAYID'),
                'PSPID' => $this->token->getPspid(),
                'PSWD' => $this->token->getApiPassword(),
                'USERID' => $this->token->getApiUser(),
            );

            $response = $this->sendApiRequest($params, $this->getDirectQueryUrl($this->debug, $this->utf8), 'GET');

            if (!$response->isSuccessful()) {
                $this->handleUnsuccessfulResponse($response, $transaction);
            } else {
                $transaction->setReferenceNumber($response->getPaymentId());
            }

        } else {
            $response = $this->feedbackResponse;
        }

        if ($response->isApproving()) {
            throw new PaymentPendingException(sprintf('Payment is still approving, status: %s.', $response->getStatus()));
        }

        if (!$response->isApproved()) {
            $ex = new FinancialException(sprintf('Payment status "%s" is not valid for approvment', $response->getStatus()));
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
     * @return mixed
     *
     * @throws ActionRequiredException If the transaction's state is NEW
     * @throws FinancialException      If payment is not approved
     * @throws PaymentPendingException If payment is still approving
     */
    public function deposit(FinancialTransactionInterface $transaction, $retry)
    {
        $this->logger->debug('depositing...');
        if ($transaction->getState() === FinancialTransactionInterface::STATE_NEW) {
            throw new ActionRequiredException('Transaction needs to be in state 4');
        }

        if (!isset($this->feedbackResponse)) {
            $paymentInstruction = $transaction->getPayment()->getPaymentInstruction();
            $this->logger->info('Building INV file...');
            $file = $this->ogoneFileBuilder->buildInv(
                $paymentInstruction->getExtendedData()->get('ORDERID'),
                $paymentInstruction->getExtendedData()->get('CLIENTID'),
                $paymentInstruction->getExtendedData()->get('ALIASID'),
                self::PAYMENT,
                $paymentInstruction->getExtendedData()->get('VAT'),
                $paymentInstruction->getExtendedData()->get('ARTICLES')
            );
            $this->logger->info('INV file content is {content}', ['content' => $file]);

            $xmlResponse = $this->sendBatchRequest($file);

            $response = new DirectResponse($xmlResponse);

            if (!$response->isSuccessful()) {
                $this->handleUnsuccessfulResponse($response, $transaction);
            } else {
                $transaction->setReferenceNumber($response->getPaymentId());
            }

        } else {
            $response = $this->feedbackResponse;
        }

        if ($response->isDepositing()) {
            throw new PaymentPendingException(sprintf('Payment is still pending, status: %s.', $response->getStatus()));
        }

        if (!$response->isDeposited()) {
            $ex = new FinancialException(sprintf('Payment status "%s" is not valid for depositing', $response->getStatus()));
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
     * @param PaymentInstructionInterface $paymentInstruction
     * @throws InvalidPaymentInstructionException
     */
    public function validatePaymentInstruction(PaymentInstructionInterface $paymentInstruction)
    {
        try {
            $this->logger->info('Building INV file...');
            $file = $this->ogoneFileBuilder->buildInv(
                $paymentInstruction->getExtendedData()->get('ORDERID'),
                $paymentInstruction->getExtendedData()->get('CLIENTID'),
                $paymentInstruction->getExtendedData()->get('ALIASID'),
                self::AUTHORIZATION,
                $paymentInstruction->getExtendedData()->get('VAT'),
                $paymentInstruction->getExtendedData()->get('ARTICLES')
            );
            $this->logger->info('INV file content is {content}', ['content' => $file]);

            $xmlResponse = $this->sendBatchRequest($file);

            $response = new DirectResponse($xmlResponse);
            if (!$response->hasError()) {
                $paymentInstruction->getExtendedData()->set('PAYID', $response->getPaymentId());
            }
        } catch (\Exception $e) {
            throw new InvalidPaymentInstructionException($e->getMessage(), $e->getCode());
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
        return 'ogone_caa' === $paymentSystemName;
    }

    /**
     * Send requests to Ogone API
     *
     * @param array $parameters
     * @param string $url
     * @param string $method
     *
     * @return \SimpleXMLElement
     *
     * @throws CommunicationException
     */
    protected function sendApiRequest(array $parameters, $url, $method = 'POST')
    {
        $response = $this->request(new Request($url, $method, $parameters));

        if (200 !== $response->getStatus()) {
            throw new CommunicationException(sprintf('The API request was not successful (Status: %s): %s', $response->getStatus(), $response->getContent()));
        }

        return new \SimpleXMLElement($response->getContent());
    }

    /**
     * @param $file
     * @param $method
     * @return \SimpleXMLElement
     * @throws CommunicationException
     */
    private function sendBatchRequest($file, $method = 'POST')
    {
        $apiData = array(
            'FILE'         => $file,
            'REPLY_TYPE'   => 'XML',
            'MODE'         => 'SYNC',
            'PROCESS_MODE' => 'CHECKANDPROCESS'
        );

        return $this->sendApiRequest($apiData, $this->getBatchUrl(), $method);
    }

    /**
     * @param \SimpleXMLElement $response
     * @param FinancialTransactionInterface $transaction
     * @throws FinancialException
     */
    private function handleUnsuccessfulResponse(\SimpleXMLElement $response, FinancialTransactionInterface $transaction)
    {
        $transaction->setResponseCode($response->getErrorCode());
        $transaction->setReasonCode($response->getStatus());

        $ex = new FinancialException('Ogone-Response was not successful: '.$response->getErrorDescription());
        $ex->setFinancialTransaction($transaction);

        throw $ex;
    }
}
