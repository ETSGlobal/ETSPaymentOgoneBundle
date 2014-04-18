<?php

namespace ETS\Payment\OgoneBundle\Response;

/*
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
 * AbstractResponse class
 *
 * @author ETSGlobal <ecs@etsglobal.org>
 */
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
    abstract public function getAmount();

    /**
     * @return string
     */
    abstract public function getPaymentId();

    /**
     * @return string
     */
    abstract public function getStatus();

    /**
     * @return integer
     */
    abstract public function getErrorCode();

    /**
     * @return string
     */
    abstract public function getErrorDescription();

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
