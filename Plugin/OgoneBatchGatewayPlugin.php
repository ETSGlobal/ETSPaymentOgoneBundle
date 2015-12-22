<?php

namespace ETS\Payment\OgoneBundle\Plugin;

use ETS\Payment\OgoneBundle\Response\BatchResponse;
use ETS\Payment\OgoneBundle\Response\DirectResponse;
use ETS\Payment\OgoneBundle\Service\OgoneFileBuilder;
use JMS\Payment\CoreBundle\BrowserKit\Request;
use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use JMS\Payment\CoreBundle\Model\PaymentInstructionInterface;
use JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException;
use JMS\Payment\CoreBundle\Plugin\Exception\CommunicationException;
use JMS\Payment\CoreBundle\Plugin\Exception\FinancialException;
use JMS\Payment\CoreBundle\Plugin\Exception\InvalidPaymentInstructionException;
use JMS\Payment\CoreBundle\Plugin\Exception\PaymentPendingException;
use JMS\Payment\CoreBundle\Plugin\PluginInterface;

use ETS\Payment\OgoneBundle\Client\TokenInterface;
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
     * @var ResponseInterface
     */
    protected $feedbackResponse;

    /**
     * @return array
     */
    public static function getAvailableOperations()
    {
        return array(
            'AUTHORIZATION'  => OgoneBatchGatewayPlugin::AUTHORIZATION,
            'PAYMENT'        => OgoneBatchGatewayPlugin::PAYMENT,
            'PARTIAL_REFUND' => OgoneBatchGatewayPlugin::PARTIAL_REFUND,
        );
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
        $this->logger->debug('approving transaction {id} with PAYID {payid}...', array('id' => $transaction->getId(), 'payid' => $transaction->getExtendedData()->get('PAYID')));
        if ($transaction->getState() === FinancialTransactionInterface::STATE_NEW) {
            $this->logger->debug('Transaction is new. Need to be pending');
            throw new ActionRequiredException('Transaction needs to be in state 4');
        }

        if (!isset($this->feedbackResponse)) {
            $this->logger->debug('No feedback response set.');
            $params  = array(
                'ORDERID' => $transaction->getExtendedData()->get('ORDERID'),
                'PAYID' => $transaction->getExtendedData()->get('PAYID'),
                'PSPID' => $this->token->getPspid(),
                'PSWD' => $this->token->getApiPassword(),
                'USERID' => $this->token->getApiUser(),
            );

            $this->logger->debug('Checking transaction status with Ogone with params {params}', array('params' => $params));
            $xmlResponse = $this->sendApiRequest(array(), $this->getDirectQueryUrl().'?'.http_build_query($params), 'GET');

            $response = new BatchResponse($xmlResponse);
            $this->logger->debug('response status is {status}', array('status' => $response->getStatus()));

            $this->setFeedbackResponse($response);
            if ($response->hasError()) {
                $this->handleUnsuccessfulResponse($response, $transaction);
            }

            $transaction->setReferenceNumber($response->getPaymentId());
        } else {
            $this->logger->debug('feedback response set.');
            $response = $this->feedbackResponse;
        }

        if ($response->isApproving() || $response->isIncomplete()) {
            $this->logger->debug('response {res} is still approving', array('res' => $response));
            throw new PaymentPendingException(sprintf('Payment is still approving, status: %s.', $response->getStatus()));
        }

        if (!$response->isApproved()) {
            $this->logger->debug('response {res} is not approved', array('res' => $response));
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
        $this->logger->debug('depositing transaction {id} with PAYID {payid}...', array('id' => $transaction->getId(), 'payid' => $transaction->getExtendedData()->get('PAYID')));
        if ($transaction->getState() === FinancialTransactionInterface::STATE_NEW) {
            throw new ActionRequiredException('Transaction needs to be in state 4');
        }

        if (!isset($this->feedbackResponse)) {
            $this->logger->debug('No feedback response set.');

            $response = $this->sendPayment($transaction);

            $this->logger->debug('response status is {status}', array('status' => $response->getStatus()));

            if ($response->hasError()) {
                $this->logger->debug(sprintf('Payment is not successful: %s', $response->getErrorDescription()));
                $this->handleUnsuccessfulResponse($response, $transaction);
            }

            $transaction->setReferenceNumber($response->getPaymentId());

        } else if (($response = $this->feedbackResponse) && $response->isAuthorized()) {
            $response = $this->sendPayment($transaction);
            $this->logger->debug('response status is {status}', array('status' => $response->getStatus()));

            if ($response->hasError()) {
                $this->logger->debug(sprintf('response is not successful! %s', $response->getErrorDescription()));
                $this->handleUnsuccessfulResponse($response, $transaction);
            }

            $transaction->setReferenceNumber($response->getPaymentId());
        }

        if ($response->isDepositing() || $response->isIncomplete()) {
            $this->logger->debug('response {res} is still depositing', array('res' => $response));
            throw new PaymentPendingException(sprintf('Payment is still pending, status: %s.', $response->getStatus()));
        }

        if (!$response->isDeposited()) {
            $this->logger->debug('response {res} is not deposited', array('res' => $response));
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
        $this->logger->debug('validating payment instruction {id}...', array('id' => $paymentInstruction->getId()));
        try {
            $file = $this->buildFile($paymentInstruction, self::AUTHORIZATION);

            $this->logger->debug('Sending authorization request to Ogone with file {file}', array('file' => $file));
            $xmlResponse = $this->sendBatchRequest($file);
            $response = new BatchResponse($xmlResponse);

            $this->logger->debug('response status is {status}', array('status' => $response->getStatus()));
            if (!$response->hasError() && $response->isIncomplete()) {
                $paymentInstruction->getExtendedData()->set('PAYID', $response->getPaymentId());
            } else {
                $paymentInstruction->getExtendedData()->set('ERROR_MESSAGE', $response->getErrorDescription());
                throw new \LogicException(sprintf('status %s, description %s.', $response->getStatus(), $response->getErrorDescription()));
            }
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Authorization failed: %s.', $e->getMessage()));
            throw new InvalidPaymentInstructionException($e->getMessage(), $e->getCode());
        }
    }

    public function reverseDeposit(FinancialTransactionInterface $transaction, $retry)
    {
        if (!isset($this->feedbackResponse)) {
            $paymentInstruction = $transaction->getPayment()->getPaymentInstruction();
            $this->logger->info('reverseDeposit: Building INV file...');
            $file = $this->ogoneFileBuilder->buildInv(
                $paymentInstruction->getExtendedData()->get('ORDERID'),
                $paymentInstruction->getExtendedData()->get('CLIENTID'),
                $paymentInstruction->getExtendedData()->get('CLIENTREF'),
                $paymentInstruction->getExtendedData()->get('ALIASID'),
                self::PARTIAL_REFUND,
                $paymentInstruction->getExtendedData()->get('ARTICLES'),
                $paymentInstruction->getExtendedData()->get('PAYID'),
                $paymentInstruction->getExtendedData()->get('TRANSACTIONID')
            );
            $this->logger->info('reverseDeposit: INV file content is {content}', array('content' => $file));

            $this->logger->debug('Sending refund request to Ogone with file {file}', array('file' => $file));
            $xmlResponse = $this->sendBatchRequest($file);

            $response = new BatchResponse($xmlResponse);

            if ($response->hasError()) {
                $this->logger->debug(sprintf('response is not successful for revert deposit: %s', $response->getErrorDescription()));
                $this->handleUnsuccessfulResponse($response, $transaction);
            }

            $transaction->setReferenceNumber($response->getPaymentId());
        } else {
            $this->logger->debug('feedback response set.');
            $response = $this->feedbackResponse;
        }

        if ($response->isRefunding() || $response->isIncomplete()) {
            $this->logger->debug('response {res} is still refunding', array('res' => $response));
            throw new PaymentPendingException(sprintf('Refund is still pending, status: %s.', $response->getStatus()));
        }

        if (!$response->isRefunded()) {
            $this->logger->debug('response {res} is not refunded', array('res' => $response));
            $ex = new FinancialException(sprintf('Refund status %s is not valid', $response->getStatus()));
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
     * @return DirectResponse
     * @throws CommunicationException
     */
    public function getTransactionStatus($params)
    {
        $xmlResponse = $this->sendApiRequest(array(), $this->getDirectQueryUrl().'?'.http_build_query($params), 'GET');

        return new DirectResponse($xmlResponse);
    }

    /**
     * @param FinancialTransactionInterface $transaction
     * @return BatchResponse
     */
    private function sendPayment(FinancialTransactionInterface $transaction)
    {
        $paymentInstruction = $transaction->getPayment()->getPaymentInstruction();
        $file = $this->buildFile($paymentInstruction, self::PAYMENT);

        $this->logger->debug('Sending payment request to Ogone with file {file}', array('file' => $file));
        $xmlResponse = $this->sendBatchRequest($file);

        return new BatchResponse($xmlResponse);
    }

    /**
     * @param PaymentInstructionInterface $paymentInstruction
     * @param string $operation
     * @return string
     */
    private function buildFile(PaymentInstructionInterface $paymentInstruction, $operation = self::PAYMENT)
    {
        $this->logger->info('Building INV file...');
        $file = $this->ogoneFileBuilder->buildInv(
            $paymentInstruction->getExtendedData()->get('ORDERID'),
            $paymentInstruction->getExtendedData()->get('CLIENTID'),
            $paymentInstruction->getExtendedData()->get('CLIENTREF'),
            $paymentInstruction->getExtendedData()->get('ALIASID'),
            $operation,
            $paymentInstruction->getExtendedData()->get('ARTICLES'),
            $paymentInstruction->getExtendedData()->get('PAYID'),
            $paymentInstruction->getExtendedData()->get('TRANSACTIONID')
        );
        $this->logger->info('INV file content is {content}', array('content' => $file));

        return $file;
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
     * @param BatchResponse $response
     * @param FinancialTransactionInterface $transaction
     * @throws FinancialException
     */
    private function handleUnsuccessfulResponse(BatchResponse $response, FinancialTransactionInterface $transaction)
    {
        $transaction->setResponseCode($response->getErrorCode());
        $transaction->setReasonCode($response->getStatusError());
        $transaction->getPayment()->getPaymentInstruction()->getExtendedData()->set('ERROR_MESSAGE', $response->getErrorDescription());
        $ex = new FinancialException('Ogone-Response was not successful: '.$response->getErrorDescription());
        $ex->setFinancialTransaction($transaction);

        throw $ex;
    }
}
