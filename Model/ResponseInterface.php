<?php

namespace ETS\Payment\OgoneBundle\Model;

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
 * Response interface
 *
 * @author ETSGlobal <e4-devteam@etsglobal.org>
 */
interface ResponseInterface
{
    const AUTHORIZED = 5;
    const REQUESTED  = 9;
    const INVALID    = 0;
    const REFUSED    = 2;
    const WAITING    = 51;
    const PROCESSING = 91;

    public function isAuthorized();
    public function isRequested();
    public function isInvalid();
    public function isRefused();
    public function isWaiting();
    public function isProcessing();
}
