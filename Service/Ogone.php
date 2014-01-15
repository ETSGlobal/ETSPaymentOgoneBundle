<?php

namespace ETS\Payment\OgoneBundle\Service;

use JMS\Payment\CoreBundle\Model\PaymentInstructionInterface;
use JMS\Payment\CoreBundle\PluginController\PluginControllerInterface;
use Symfony\Component\HttpFoundation\Request;

use ETS\Payment\OgoneBundle\Model\HashGenerator;
use ETS\Payment\OgoneBundle\Response\FeedbackResponse;

class Ogone
{
    protected $pluginController;
    protected $generator;
    protected $feedbackResponse;

    public function __construct(PluginControllerInterface $pluginController, HashGenerator $generator, Request $request)
    {
        $this->pluginController = $pluginController;
        $this->generator = $generator;
        $this->feedbackResponse = new FeedbackResponse($request);
    }

    public function handleTransactionFeedback(PaymentInstructionInterface $instruction)
    {
        if (!$this->hasValidHash()) {
            throw new \LogicException('[Ogone - callback] hash verification failed');
        }

        if (null === $transaction = $instruction->getPendingTransaction()) {
            throw new \LogicException('[Ogone - callback] no pending transaction found for the payment instruction');
        }

        $transaction->getExtendedData()->set('feedbackResponse', $this->feedbackResponse);
        $transaction->setReferenceNumber($this->feedbackResponse->getPaymentId());

        $this->pluginController->approveAndDeposit($transaction->getPayment()->getId(), $this->feedbackResponse->getAmount());
    }

    protected function hasValidHash()
    {
        return $this->generator->generate($this->feedbackResponse->getValues()) === $this->feedbackResponse->getHash();
    }
}
