<?php

namespace ETS\Payment\OgoneBundle\Client;

use ETS\Payment\OgoneBundle\Client\TokenInterface;

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
 * Token
 *
 * @author ETSGlobal <ecs@etsglobal.org>
 */
class Token implements TokenInterface
{
    protected $pspid;
    protected $apiPassword;
    protected $apiUser;
    protected $shain;
    protected $shaout;

    /**
     * @param string $pspid       The PSPID
     * @param string $apiUser     The API user
     * @param string $apiPassword The API user password
     * @param string $shain       The sha in
     * @param string $shaout      The sha out
     */
    public function __construct($pspid, $apiUser, $apiPassword, $shain, $shaout)
    {
        $this->pspid = $pspid;
        $this->apiUser = $apiUser;
        $this->apiPassword = $apiPassword;
        $this->shain = $shain;
        $this->shaout = $shaout;
    }

    /**
     * @return string
     */
    public function getPspid()
    {
        return $this->pspid;
    }

    /**
     * @return string
     */
    public function getApiUser()
    {
        return $this->apiUser;
    }

    /**
     * @return string
     */
    public function getApiPassword()
    {
        return $this->apiPassword;
    }

    /**
     * @return string
     */
    public function getShain()
    {
        return $this->shain;
    }

    /**
     * @return string
     */
    public function getShaout()
    {
        return $this->shaout;
    }
}
