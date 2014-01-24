<?php

namespace ETS\Payment\OgoneBundle\Hash;

/*
 * Copyright 2014 ETSGlobal <e4-devteam@etsglobal.org>
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
 * Sha-1 Out generator
 *
 * @author ETSGlobal <e4-devteam@etsglobal.org>
 */
class Sha1Out implements GeneratorInterface
{
    private $passphrase;

    public static $acceptableFields = array(
        'AAVADDRESS',
        'AAVCHECK',
        'AAVZIP',
        'ACCEPTANCE',
        'ALIAS',
        'amount',
        'BIN',
        'BRAND',
        'CARDNO',
        'CCCTY',
        'CN',
        'COMPLUS',
        'CREATION_STATUS',
        'currency',
        'CVCCHECK',
        'DCC_COMMPERCENTAGE',
        'DCC_CONVAMOUNT',
        'DCC_CONVCCY',
        'DCC_EXCHRATE',
        'DCC_EXCHRATESOURCE',
        'DCC_EXCHRATETS',
        'DCC_INDICATOR',
        'DCC_MARGINPERCENTAGE',
        'DCC_VALIDHOURS',
        'DIGESTCARDNO',
        'ECI',
        'ED',
        'ENCCARDNO',
        'FXAMOUNT',
        'FXCURRENCY',
        'IP',
        'IPCTY',
        'NBREMAILUSAGE',
        'NBRIPUSAGE',
        'NBRIPUSAGE_ALLTX',
        'NBRUSAGE',
        'NCERROR',
        'NCERRORCARDNO',
        'NCERRORCN',
        'NCERRORCVC',
        'NCERRORED',
        'orderID',
        'PAYID',
        'PM',
        'SCO_CATEGORY',
        'SCORING',
        'STATUS',
        'SUBBRAND',
        'SUBSC RIPTION_ID',
        'TRXDATE',
        'VC',
    );

    /**
     * @param string $passphrase
     */
    public function __construct($passphrase)
    {
        $this->passphrase = $passphrase;
    }

    /**
     * @param array $parameters
     *
     * @return string
     */
    public function generate(array $parameters)
    {
        $stringToHash = $this->getStringToHash($parameters);

        return strtoupper(sha1($stringToHash));
    }

    protected function getStringToHash(array $parameters)
    {
        $stringToHash = '';
        $parameters   = array_change_key_case($parameters, CASE_UPPER);

        foreach (self::$acceptableFields as $acceptableField) {
            if (isset($parameters[strtoupper($acceptableField)]) && (string) $parameters[strtoupper($acceptableField)] !== '') {
                $stringToHash .= sprintf('%s=%s%s', strtoupper($acceptableField), $parameters[strtoupper($acceptableField)], $this->passphrase);
            }
        }

        return $stringToHash;
    }
}
