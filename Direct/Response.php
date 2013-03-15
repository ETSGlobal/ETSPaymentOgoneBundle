<?php

namespace ETS\Payment\OgoneBundle\Direct;

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
 * Response class
 *
 * @author ETSGlobal <e4-devteam@etsglobal.org>
 */
class Response implements ResponseInterface
{
    /**
     * @var \SimpleXMLElement
     */
    public $xml;

    /**
     * @param \SimpleXMLElement $parameters
     */
    public function __construct(\SimpleXMLElement $xml)
    {
        $this->xml = $xml;
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
    public function isDepositing()
    {
        // When the payment is approved, it is in the depositing state

        return in_array($this->getStatus(), array_merge(
            $this->getApprovedStatuses(),
            $this->getDepositingStatuses()
        ), true);
    }

    /**
     * @return boolean
     */
    public function isApproved()
    {
        // When the payment is already in a depositing state its mean that
        // it have already been approved

        return in_array($this->getStatus(), array_merge(
            $this->getApprovedStatuses(),
            $this->getDepositedStatuses(),
            $this->getDepositingStatuses()
        ), true);
    }

    /**
     * @return boolean
     */
    public function isApproving()
    {
        return in_array($this->getStatus(), $this->getApprovingStatuses(), true);
    }

    /**
     * @return string
     */
    public function getPaymentId()
    {
        return (string) $this->xml->attributes()->PAYID;
    }

    /**
     * @return boolean
     */
    public function isSuccessful()
    {
        return !in_array($this->getStatus(), array(static::INVALID, '', null), true);
    }

    /**
     * @return string
     */
    public function getAmount()
    {
        return (float) $this->xml->attributes()->amount;
    }

    /**
     * @return integer
     */
    public function getStatus()
    {
        return (int) $this->xml->attributes()->STATUS;
    }

    /**
     * @return integer
     */
    public function getErrorCode()
    {
        return (string) $this->xml->attributes()->NCERROR;
    }

    /**
     * @return string
     */
    public function getErrorDescription()
    {
        return (string) $this->xml->attributes()->NCERRORPLUS;
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
