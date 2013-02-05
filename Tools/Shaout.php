<?php

namespace ETS\Payment\OgoneBundle\Tools;

use ETS\Payment\OgoneBundle\Client\TokenInterface;

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
 * Sha-1 Out tool
 *
 * @author ETSGlobal <e4-devteam@etsglobal.org>
 */
class Shaout
{
    protected static $allowed = array(
        'AAVADDRESS', 'AAVCHECK', 'AAVZIP', 'ACCEPTANCE', 'ALIAS', 'AMOUNT',
        'BIN', 'BRAND', 'CARDNO', 'CCCTY', 'CN', 'COMPLUS', 'CREATION_STATUS',
        'CURRENCY', 'CVCCHECK', 'DCC_COMMPERCENTAGE', 'DCC_CONVAMOUNT',
        'DCC_CONVCCY', 'DCC_EXCHRATE', 'DCC_EXCHRATESOURCE', 'DCC_EXCHRATETS',
        'DCC_INDICATOR', 'DCC_MARGINPERCENTAGE', 'DCC_VALIDHOURS',
        'DIGESTCARDNO', 'ECI', 'ED', 'ENCCARDNO', 'FXAMOUNT', 'FXCURRENCY',
        'IP', 'IPCTY', 'NBREMAILUSAGE', 'NBRIPUSAGE', 'NBRIPUSAGE_ALLTX',
        'NBRUSAGE', 'NCERROR', 'ORDERID', 'PAYID', 'PM', 'SCO_CATEGORY',
        'SCORING', 'STATUS', 'SUBBRAND', 'SUBSCRIPTION_ID', 'TRXDATE', 'VC'
    );

    /**
     * @var TokenInterface
     */
    protected $token;

    /**
     * @param TokenInterface $token
     */
    public function __construct(TokenInterface $token)
    {
        $this->token = $token;
    }

    /**
     * @param array $parameters
     *
     * @return string
     */
    public function generate(array $parameters)
    {
        $shaoutString ='';

        // All parameters need to be arranged alphabetically.
        ksort($parameters);

        foreach ($parameters as $key => $value) {

            // Parameters that do not have a value should NOT be included in the string to hash
            if (empty($value)) {
                continue;
            }

            // All parameter names should be in UPPERCASE (to avoid any case confusion).
            $key = strtoupper($key);

            if (in_array($key, static::$allowed, true)) {
                $shaoutString .= $key.'='.$value.$this->token->getShaout();
            }
        }

        return sha1($shaoutString);
    }
}