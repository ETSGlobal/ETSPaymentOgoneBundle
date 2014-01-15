<?php

namespace ETS\Payment\OgoneBundle\Response;

abstract class AbstractResponse implements ResponseInterface
{
    /**
     * @return boolean
     */
    public function isApproving()
    {
        return in_array($this->getStatus(), $this->getApprovingStatuses(), true);
    }

    /**
     * Returns true if the payment is approved or already in a depositing state.
     *
     * @return boolean
     */
    public function isApproved()
    {
        return in_array($this->getStatus(), array_merge(
            $this->getApprovedStatuses(),
            $this->getDepositedStatuses(),
            $this->getDepositingStatuses()
        ), true);
    }

    /**
     * Returns true if the payment is in a depositing state or approved.
     *
     * @return boolean
     */
    public function isDepositing()
    {
        return in_array($this->getStatus(), array_merge(
            $this->getApprovedStatuses(),
            $this->getDepositingStatuses()
        ), true);
    }

    /**
     * @return boolean
     */
    public function isDeposited()
    {
        return in_array($this->getStatus(), $this->getDepositedStatuses(), true);
    }

    /**
     * @return boolean
     */
    public function isSuccessful()
    {
        return !in_array($this->getStatus(), array(static::INVALID, '', null), true);
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        throw new \Exception('This function must be defined in a child class.');
    }

    /**
     * @return string
     */
    public function getPaymentId()
    {
        throw new \Exception('This function must be defined in a child class.');
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        throw new \Exception('This function must be defined in a child class.');
    }

    /**
     * @return integer
     */
    public function getErrorCode()
    {
        throw new \Exception('This function must be defined in a child class.');
    }

    /**
     * @return string
     */
    public function getErrorDescription()
    {
        throw new \Exception('This function must be defined in a child class.');
    }

    /**
     * @return array
     */
    protected function getApprovingStatuses()
    {
        return array(
            static::AUTHORIZATION_MANUALLY,
            static::AUTHORIZATION_UNKNOWN,
            static::AUTHORIZATION_WAITING,
        );
    }

    /**
     * @return array
     */
    protected function getApprovedStatuses()
    {
        return array(
            static::AUTHORIZED,
        );
    }

    /**
     * @return array
     */
    protected function getDepositingStatuses()
    {
        return array(
            static::PAYMENT_UNCERTAIN,
            static::PAYMENT_PROCESSING,
            static::PAYMENT_PROCESSING_1,
            static::PAYMENT_PROCESSING_2,
            static::PAYMENT_PROCESSING_3,
            static::WAITING_CLIENT_PAYMENT,
            static::STORED,
        );
    }

    /**
     * @return array
     */
    protected function getDepositedStatuses()
    {
        return array(
            static::PAYMENT_PROCESSED,
            static::PAYMENT_REQUESTED,
        );
    }
}
