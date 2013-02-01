<?php

namespace ETS\Payment\OgoneBundle\Gateway;

use Symfony\Component\HttpFoundation\ParameterBag;

use ETS\Payment\OgoneBundle\Model\ResponseInterface;

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
    public $body;

    public function __construct(array $parameters)
    {
        $this->body = new ParameterBag($parameters);
    }

    public function isAuthorized()
    {
        return self::AUTHORIZED ===  $this->body->get('STATUS');
    }

    public function isRequested()
    {
        return self::REQUESTED === $this->body->get('STATUS');
    }

    public function isInvalid()
    {
        return self::INVALID === $this->body->get('STATUS');
    }

    public function isRefused()
    {
        return self::REFUSED === $this->body->get('STATUS');
    }

    public function isWaiting()
    {
        return self::WAITING === $this->body->get('STATUS');
    }

    public function isProcessing()
    {
        return self::PROCESSING === $this->body->get('STATUS');
    }

    public function getErrors()
    {
        return $this->body->get('NCERROR');
    }
}
