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
 * Response interface
 *
 * @author ETSGlobal <ecs@etsglobal.org>
 */
interface ResponseInterface
{
    const INVALID                           = 0;
    const CANCELLED                         = 1;
    const AUTHORIZATION_REFUSED             = 2;
    const STORED                            = 4;
    const WAITING_CLIENT_PAYMENT            = 41;
    const AUTHORIZED                        = 5;
    const AUTHORIZATION_WAITING             = 51;
    const AUTHORIZATION_UNKNOWN             = 52;
    const AUTHORIZATION_MANUALLY            = 59;
    const AUTHORIZED_AND_CANCELLED          = 6;
    const AUTHORIZATION_DELETION_WAITING    = 61;
    const AUTHORIZATION_DELETION_UNCERTAIN  = 62;
    const AUTHORIZATION_DELETION_REFUSED    = 63;
    const PAYMENT_DELETED                   = 7;
    const PAYMENT_DELETION_PENDING          = 71;
    const PAYMENT_DELETION_UNCERTAIN        = 72;
    const PAYMENT_DELETION_REFUSED          = 73;
    const PAYMENT_DELETED_NOT_ACCEPTED      = 74;
    const DELETED                           = 75;
    const REFUND                            = 8;
    const REFUND_PENDING                    = 81;
    const REFUND_UNCERTAIN                  = 82;
    const REFUND_REFUSED                    = 83;
    const PAYMENT_DECLINED                  = 84;
    const REFUND_DECLINED                   = 94;
    const REFUNDED                          = 85;
    const PAYMENT_REQUESTED                 = 9;
    const PAYMENT_PROCESSING                = 91;
    const PAYMENT_UNCERTAIN                 = 92;
    const PAYMENT_REFUSED                   = 93;
    const PAYMENT_PROCESSED                 = 95;
    const PAYMENT_PROCESSING_1              = 97;
    const PAYMENT_PROCESSING_2              = 98;
    const PAYMENT_PROCESSING_3              = 99;

    public function isApproving();
    public function isApproved();

    public function isDepositing();
    public function isDeposited();

    public function isSuccessful();

    public function getAmount();
    public function getPaymentId();
    public function getStatus();

    public function getErrorCode();
    public function getErrorDescription();
}
