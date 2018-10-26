<?php

namespace ETS\Payment\OgoneBundle\Plugin\Configuration;

use JMS\Payment\CoreBundle\Model\ExtendedDataInterface;

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
     * @param ExtendedDataInterface $data
     *
     * @return string|null
     */
    public function getAcceptUrl(ExtendedDataInterface $data): ?string
    {
        return $data->has('acceptUrl') ? $data->get('acceptUrl') : $this->acceptUrl;
    }

    /**
     * @param ExtendedDataInterface $data
     *
     * @return string|null
     */
    public function getDeclineUrl(ExtendedDataInterface $data): ?string
    {
        return $data->has('declineUrl') ? $data->get('declineUrl') : $this->declineUrl;
    }

    /**
     * @param ExtendedDataInterface $data
     *
     * @return string|null
     */
    public function getExceptionUrl(ExtendedDataInterface $data): ?string
    {
        return $data->has('exceptionUrl') ? $data->get('exceptionUrl') : $this->exceptionUrl;
    }

    /**
     * @param ExtendedDataInterface $data
     *
     * @return string|null
     */
    public function getCancelUrl(ExtendedDataInterface $data): ?string
    {
        return $data->has('cancelUrl') ? $data->get('cancelUrl') : $this->cancelUrl;
    }

    /**
     * @param ExtendedDataInterface $data
     *
     * @return string|null
     */
    public function getBackUrl(ExtendedDataInterface $data): ?string
    {
        return $data->has('backUrl') ? $data->get('backUrl') : $this->backUrl;
    }

    /**
     * @param ExtendedDataInterface $data
     *
     * @return array
     */
    public function getRequestParameters(ExtendedDataInterface $data): array
    {
        return [
            'ACCEPTURL' => $this->getAcceptUrl($data),
            'DECLINEURL' => $this->getDeclineUrl($data),
            'EXCEPTIONURL' => $this->getExceptionUrl($data),
            'CANCELURL' => $this->getCancelUrl($data),
            'BACKURL' => $this->getBackUrl($data),
        ];
    }
}
