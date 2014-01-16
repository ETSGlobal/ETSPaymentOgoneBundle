<?php

namespace ETS\Payment\OgoneBundle\Hash;

/**
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

    private $allowed = array(
        'AAVADDRESS',
        'AAVC HEC K',
        'AAVZIP',
        'AC C EPTANC E',
        'ALIAS',
        'AMOUNT',
        'BIN',
        'BRAND',
        'C ARDNO',
        'C C C TY',
        'CN',
        'C OMPLUS',
        'C REATION_STATUS',
        'C URRENC Y',
        'C VC C HEC K',
        'DC C _C OMMPERC ENTAGE',
        'DC C _C ONVAMOUNT',
        'DC C _C ONVC C Y',
        'DC C _EXC HRATE',
        'DC C _EXC HRATESOURC E',
        'DC C _EXC HRATETS',
        'DC C _INDIC ATOR',
        'DC C _MARGINPERC ENTAGE',
        'DC C _VALIDHOURS',
        'DIGESTC ARDNO',
        'EC I',
        'ED',
        'ENC C ARDNO',
        'FXAMOUNT',
        'FXC URRENC Y',
        'IP',
        'IPC TY',
        'NBREMAILUSAGE',
        'NBRIPUSAGE',
        'NBRIPUSAGE_ALLTX',
        'NBRUSAGE',
        'NC ERROR',
        'NC ERRORC ARDNO',
        'NC ERRORC N',
        'NC ERRORC VC',
        'NC ERRORED',
        'ORDERID',
        'PAYID',
        'PM',
        'SC O_C ATEGORY',
        'SC ORING',
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
        $stringToHash = '';

        // All parameters need to be arranged alphabetically.
        ksort($parameters);

        foreach ($parameters as $field => $value) {
            // Parameters that do not have a value should NOT be included in the string to hash
            if (empty($value)) {
                continue;
            }

            // All parameter names should be in UPPERCASE (to avoid any case confusion).
            $field = strtoupper($field);

            if (in_array($field, $this->allowed, true)) {
                $stringToHash .= sprintf('%s=%s%s', $field, $value, $passphrase);
            }
        }

        return strtoupper(sha1($stringToHash));
    }
}
