<?php

namespace ETS\Payment\OgoneBundle\Response;

use Symfony\Component\HttpFoundation\Request;

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
 * FeedbackResponse class
 *
 * @author ETSGlobal <e4-devteam@etsglobal.org>
 */
class FeedbackResponse extends AbstractResponse
{
    private static $fields = array(
        'orderID',
        'amount',
        'currency',
        'PM',
        'ACCEPTANCE',
        'STATUS',
        'CARDNO',
        'PAYID',
        'NCERROR',
        'BRAND',
        'SHASIGN',
    );

    private $values = array();
    private $hash;

    /**
     * FeedbackResponse constructor
     *
     * @param  array $feedback optional to allow instanciation followed by a call to setValuesFromRequest()
     */
    public function __construct(array $feedback = array())
    {
        foreach ($feedback as $field => $value) {
            $this->addValue($field, $value);
        }
    }

    /**
     * sets feedback values from a Request
     *
     * @param  Request $request
     */
    public function setValuesFromRequest(Request $request)
    {
        foreach (self::$fields as $field) {
            if ($request->get($field)) {
                $this->addValue($field, $request->get($field));
            }
        }
    }

    public function getValues()
    {
        return $this->values;
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function getOrderId()
    {
        return $this->getValue('orderID');
    }

    public function getPaymentId()
    {
        return $this->getValue('PAYID');
    }

    public function getAmount()
    {
        return $this->getValue('amount');
    }

    public function getStatus()
    {
        return $this->getValue('status');
    }

    public function getErrorCode()
    {
        return $this->getValue('NCERROR');
    }

    public function getErrorDescription()
    {
        return $this->getValue('NCERRORPLUS');
    }

    /**
     * @param string $field
     * @param mixed $value
     *
     * @throws \BadMethodCallException If we try to set the value of a field twice
     */
    private function addValue($field, $value)
    {
        $field = strtoupper($field);

        if (isset($this->values[$field])) {
            throw new \BadMethodCallException(sprintf('Feedback parameter [%s] already set.', $field));
        }

        if ('SHASIGN' !== $field) {
            $this->values[$field] = $value;
        } else {
            $this->hash = $value;
        }
    }

    /**
     * @param  string $field
     *
     * @return string|integer
     *
     * @throws \OutOfRangeException If the given field has not been set when this object has been instanciated
     */
    private function getValue($field)
    {
        $field = strtoupper($field);

        if (!isset($this->values[$field])) {
            throw new \OutOfRangeException(sprintf('Feedback parameter [%s] was not sent with the Request.', $field));
        }

        return $this->values[$field];
    }
}
