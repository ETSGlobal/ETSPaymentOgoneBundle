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
    public const INVALID                           = 0;
    public const CANCELLED                         = 1;
    public const AUTHORIZATION_REFUSED             = 2;
    public const STORED                            = 4;
    public const WAITING_CLIENT_PAYMENT            = 41;
    public const AUTHORIZED                        = 5;
    public const AUTHORIZATION_WAITING             = 51;
    public const AUTHORIZATION_UNKNOWN             = 52;
    public const AUTHORIZATION_MANUALLY            = 59;
    public const AUTHORIZED_AND_CANCELLED          = 6;
    public const AUTHORIZATION_DELETION_WAITING    = 61;
    public const AUTHORIZATION_DELETION_UNCERTAIN  = 62;
    public const AUTHORIZATION_DELETION_REFUSED    = 63;
    public const PAYMENT_DELETED                   = 7;
    public const PAYMENT_DELETION_PENDING          = 71;
    public const PAYMENT_DELETION_UNCERTAIN        = 72;
    public const PAYMENT_DELETION_REFUSED          = 73;
    public const PAYMENT_DELETED_NOT_ACCEPTED      = 74;
    public const DELETED                           = 75;
    public const REFUND                            = 8;
    public const REFUND_PENDING                    = 81;
    public const REFUND_UNCERTAIN                  = 82;
    public const REFUND_REFUSED                    = 83;
    public const PAYMENT_DECLINED                  = 84;
    public const REFUND_DECLINED                   = 94;
    public const REFUNDED                          = 85;
    public const PAYMENT_REQUESTED                 = 9;
    public const PAYMENT_PROCESSING                = 91;
    public const PAYMENT_UNCERTAIN                 = 92;
    public const PAYMENT_REFUSED                   = 93;
    public const PAYMENT_PROCESSED                 = 95;
    public const PAYMENT_PROCESSING_1              = 97;
    public const PAYMENT_PROCESSING_2              = 98;
    public const PAYMENT_PROCESSING_3              = 99;

    public function isApproving();
    public function isApproved();

    public function isDepositing();
    public function isDeposited();

    public function isSuccessful();

    public function getAmount();
    public function getPaymentId();
    public function getStatus(): int;

    public function getErrorCode(): string;
    public function getErrorDescription();
}
