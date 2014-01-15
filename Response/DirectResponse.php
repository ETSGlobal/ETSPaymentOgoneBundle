<?php

namespace ETS\Payment\OgoneBundle\Response;

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
class DirectResponse extends AbstractResponse
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
     * @return string
     */
    public function getPaymentId()
    {
        return (string) $this->xml->attributes()->PAYID;
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
}