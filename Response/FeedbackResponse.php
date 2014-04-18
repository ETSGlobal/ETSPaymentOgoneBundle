<?php

namespace ETS\Payment\OgoneBundle\Response;

use Symfony\Component\HttpFoundation\Request;

use ETS\Payment\OgoneBundle\Hash\Sha1Out;

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
 * FeedbackResponse class
 *
 * @author ETSGlobal <ecs@etsglobal.org>
 */
class FeedbackResponse extends AbstractResponse
{
    private $values = array();
    private $hash;

    /**
     * FeedbackResponse constructor
     *
     * @param  Request $request
     */
    public function __construct(Request $request)
    {
        foreach (array_merge($request->query->all(), $request->request->all()) as $receivedField => $value) {
            if (Sha1Out::isAcceptableField($receivedField)) {
                if ((string) $value !== '') {
                    $this->addValue($receivedField, $value);
                }
            } elseif ('SHASIGN' === strtoupper($receivedField) && null !== $value) {
                $this->hash = $value;   // SHASIGN is not part of the acceptable fields for the calculation of the hash
            }
        }
    }

    public function getValues()
    {
        return $this->values['received'];
    }

    public function getHash()
    {
        if (!isset($this->hash)) {
            throw new \LogicException('The hash is not set');
        }

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
        if (isset($this->values['uppercased'][strtoupper($field)])) {
            throw new \BadMethodCallException(sprintf('Feedback parameter [%s] already set.', $field));
        }

        $this->values['received'][$field] =
        $this->values['uppercased'][strtoupper($field)] = $value;
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

        if (!isset($this->values['uppercased'][strtoupper($field)])) {
            throw new \OutOfRangeException(sprintf('Feedback parameter [%s] was not sent with the Request.', $field));
        }

        return $this->values['uppercased'][strtoupper($field)];
    }
}
