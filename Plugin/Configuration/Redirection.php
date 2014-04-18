<?php

namespace ETS\Payment\OgoneBundle\Plugin\Configuration;

use JMS\Payment\CoreBundle\Entity\ExtendedData;

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
 * Redirection class
 *
 * @author ETSGlobal <ecs@etsglobal.org>
 */
class Redirection
{
    protected $acceptUrl;
    protected $declineUrl;
    protected $exceptionUrl;
    protected $cancelUrl;
    protected $backUrl;

    /**
     * @param string $acceptUrl
     * @param string $declineUrl
     * @param string $exceptionUrl
     * @param string $cancelUrl
     * @param string $backUrl
     */
    public function __construct($acceptUrl = null, $declineUrl = null, $exceptionUrl = null, $cancelUrl = null, $backUrl = null)
    {
        $this->acceptUrl = $acceptUrl;
        $this->declineUrl = $declineUrl;
        $this->exceptionUrl = $exceptionUrl;
        $this->cancelUrl = $cancelUrl;
        $this->backUrl = $backUrl;
    }

    /**
     * @param \JMS\Payment\CoreBundle\Entity\ExtendedData $data
     *
     * @return string|null
     */
    public function getAcceptUrl(ExtendedData $data)
    {
        return $data->has('acceptUrl') ? $data->get('acceptUrl') : $this->acceptUrl;
    }

    /**
     * @param \JMS\Payment\CoreBundle\Entity\ExtendedData $data
     *
     * @return string|null
     */
    public function getDeclineUrl(ExtendedData $data)
    {
        return $data->has('declineUrl') ? $data->get('declineUrl') : $this->declineUrl;
    }

    /**
     * @param \JMS\Payment\CoreBundle\Entity\ExtendedData $data
     *
     * @return string|null
     */
    public function getExceptionUrl(ExtendedData $data)
    {
        return $data->has('exceptionUrl') ? $data->get('exceptionUrl') : $this->exceptionUrl;
    }

    /**
     * @param \JMS\Payment\CoreBundle\Entity\ExtendedData $data
     *
     * @return string|null
     */
    public function getCancelUrl(ExtendedData $data)
    {
        return $data->has('cancelUrl') ? $data->get('cancelUrl') : $this->cancelUrl;
    }

    /**
     * @param \JMS\Payment\CoreBundle\Entity\ExtendedData $data
     *
     * @return string|null
     */
    public function getBackUrl(ExtendedData $data)
    {
        return $data->has('backUrl') ? $data->get('backUrl') : $this->backUrl;
    }

    /**
     * @param \JMS\Payment\CoreBundle\Entity\ExtendedData $data
     *
     * @return array
     */
    public function getRequestParameters(ExtendedData $data)
    {
        return array(
            "ACCEPTURL"    => $this->getAcceptUrl($data),
            "DECLINEURL"   => $this->getDeclineUrl($data),
            "EXCEPTIONURL" => $this->getExceptionUrl($data),
            "CANCELURL"    => $this->getCancelUrl($data),
            "BACKURL"      => $this->getBackUrl($data),
        );
    }
}
