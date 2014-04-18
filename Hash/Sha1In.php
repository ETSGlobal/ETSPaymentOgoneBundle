<?php

namespace ETS\Payment\OgoneBundle\Hash;

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
 * Sha-1 In tool
 *
 * @author ETSGlobal <ecs@etsglobal.org>
 */
class Sha1In implements GeneratorInterface
{
    protected static $allowed = array(
        'ACCEPTANCE', 'ACCEPTURL', 'ADDMATCH', 'ADDRMATCH', 'AIACTIONNUMBER',
        'AIAGIATA', 'AIAIRNAME', 'AIAIRTAX', 'AICHDET', 'AICONJTI',
        'AIDEPTCODE', 'AIEYCD', 'AIGLNUM', 'AIINVOICE', 'AIIRST', 'AIPASNAME',
        'AIPROJNUM', 'AITIDATE', 'AITINUM', 'AITYPCH', 'AIVATAMNT', 'AIVATAPPL',
        'ALIAS', 'ALIASOPERATION', 'ALIASUSAGE', 'ALLOWCORRECTION', 'AMOUNT',
        'AMOUNTHTVA', 'AMOUNTTVA', 'BACKURL', 'BATCHID', 'BGCOLOR', 'BLVERNUM',
        'BIN', 'BRAND', 'BRANDVISUAL', 'BUTTONBGCOLOR', 'BUTTONTXTCOLOR',
        'CANCELURL', 'CARDNO', 'CATALOGURL', 'CAVV_3D', 'CAVVALGORITHM_3D',
        'CERTID', 'CHECK_AAV', 'CIVILITY', 'CN', 'COM', 'COMPLUS', 'CONVCCY',
        'COSTCENTER', 'COSTCODE', 'CREDITCODE', 'CUID', 'CURRENCY', 'CVC',
        'CVCFLAG', 'DATA', 'DATATYPE', 'DATEIN', 'DATEOUT', 'DCC_COMMPERC',
        'DCC_CONVAMOUNT', 'DCC_CONVCCY', 'DCC_EXCHRATE', 'DCC_EXCHRATETS',
        'DCC_INDICATOR', 'DCC_MARGINPERC', 'DCC_REF', 'DCC_SOURCE', 'DCC_VALID',
        'DECLINEURL', 'DEVICE', 'DISCOUNTRATE', 'DISPLAYMODE', 'ECI', 'ECI_3D',
        'ECOM_BILLTO_POSTAL_CITY', 'ECOM_BILLTO_POSTAL_COUNTRYCODE',
        'ECOM_BILLTO_POSTAL_NAME_FIRST', 'ECOM_BILLTO_POSTAL_NAME_LAST',
        'ECOM_BILLTO_POSTAL_POSTALCODE', 'ECOM_BILLTO_POSTAL_STREET_LINE1',
        'ECOM_BILLTO_POSTAL_STREET_LINE2', 'ECOM_BILLTO_POSTAL_STREET_NUMBER',
        'ECOM_CONSUMERID', 'ECOM_CONSUMER_GENDER', 'ECOM_CONSUMEROGID',
        'ECOM_CONSUMERORDERID', 'ECOM_CONSUMERUSERALIAS',
        'ECOM_CONSUMERUSERPWD', 'ECOM_CONSUMERUSERID',
        'ECOM_PAYMENT_CARD_EXPDATE_MONTH', 'ECOM_PAYMENT_CARD_EXPDATE_YEAR',
        'ECOM_PAYMENT_CARD_NAME', 'ECOM_PAYMENT_CARD_VERIFICATION',
        'ECOM_SHIPTO_COMPANY', 'ECOM_SHIPTO_DOB', 'ECOM_SHIPTO_ONLINE_EMAIL',
        'ECOM_SHIPTO_POSTAL_CITY', 'ECOM_SHIPTO_POSTAL_COUNTRYCODE',
        'ECOM_SHIPTO_POSTAL_NAME_FIRST', 'ECOM_SHIPTO_POSTAL_NAME_LAST',
        'ECOM_SHIPTO_POSTAL_NAME_PREFIX', 'ECOM_SHIPTO_POSTAL_POSTALCODE',
        'ECOM_SHIPTO_POSTAL_STREET_LINE1', 'ECOM_SHIPTO_POSTAL_STREET_LINE2',
        'ECOM_SHIPTO_POSTAL_STREET_NUMBER', 'ECOM_SHIPTO_TELECOM_FAX_NUMBER',
        'ECOM_SHIPTO_TELECOM_PHONE_NUMBER', 'ECOM_SHIPTO_TVA', 'ED', 'EMAIL',
        'EXCEPTIONURL', 'EXCLPMLIST', 'FIRSTCALL', 'FLAG3D', 'FONTTYPE',
        'FORCECODE1', 'FORCECODE2', 'FORCECODEHASH', 'FORCEPROCESS', 'FORCETP',
        'GENERIC_BL', 'GIROPAY_ACCOUNT_NUMBER', 'GIROPAY_BLZ',
        'GIROPAY_OWNER_NAME', 'GLOBORDERID', 'GUID', 'HDFONTTYPE',
        'HDTBLBGCOLOR', 'HDTBLTXTCOLOR', 'HEIGHTFRAME', 'HOMEURL',
        'HTTP_ACCEPT', 'HTTP_USER_AGENT', 'INCLUDE_BIN', 'INCLUDE_COUNTRIES',
        'INVDATE', 'INVDISCOUNT', 'INVLEVEL', 'INVORDERID', 'ISSUERID',
        'IST_MOBILE', 'ITEM_COUNT', 'LANGUAGE', 'LEVEL1AUTHCPC',
        'LIMITCLIENTSCRIPTUSAGE', 'LINE_REF', 'LINE_REF1', 'LINE_REF2',
        'LINE_REF3', 'LINE_REF4', 'LINE_REF5', 'LINE_REF6', 'LIST_BIN',
        'LIST_COUNTRIES', 'LOGO', 'MERCHANTID', 'MODE', 'MTIME', 'MVER',
        'NETAMOUNT', 'OPERATION', 'ORDERID', 'ORDERSHIPCOST', 'ORDERSHIPMETH',
        'ORDERSHIPTAX', 'ORDERSHIPTAXCODE', 'ORIG', 'OR_INVORDERID',
        'OR_ORDERID', 'OWNERADDRESS', 'OWNERADDRESS2', 'OWNERCTY', 'OWNERTELNO',
        'OWNERTELNO2', 'OWNERTOWN', 'OWNERZIP', 'PAIDAMOUNT', 'PARAMPLUS',
        'PARAMVAR', 'PAYID', 'PAYMETHOD', 'PM', 'PMLIST', 'PMLISTPMLISTTYPE',
        'PMLISTTYPE', 'PMLISTTYPEPMLIST', 'PMTYPE', 'POPUP', 'POST', 'PSPID',
        'PSWD', 'REF', 'REFER', 'REFID', 'REFKIND', 'REF_CUSTOMERID',
        'REF_CUSTOMERREF', 'REGISTRED', 'REMOTE_ADDR', 'REQGENFIELDS',
        'RTIMEOUT', 'RTIMEOUTREQUESTEDTIMEOUT', 'SCORINGCLIENT', 'SETT_BATCH',
        'SID', 'STATUS_3D', 'SUBSCRIPTION_ID', 'SUB_AM', 'SUB_AMOUNT',
        'SUB_COM', 'SUB_COMMENT', 'SUB_CUR', 'SUB_ENDDATE', 'SUB_ORDERID',
        'SUB_PERIOD_MOMENT', 'SUB_PERIOD_MOMENT_M', 'SUB_PERIOD_MOMENT_WW',
        'SUB_PERIOD_NUMBER', 'SUB_PERIOD_NUMBER_D', 'SUB_PERIOD_NUMBER_M',
        'SUB_PERIOD_NUMBER_WW', 'SUB_PERIOD_UNIT', 'SUB_STARTDATE',
        'SUB_STATUS', 'TAAL', 'TBLBGCOLOR', 'TBLTXTCOLOR', 'TID', 'TITLE',
        'TOTALAMOUNT', 'TP', 'TRACK2', 'TXTBADDR2', 'TXTCOLOR', 'TXTOKEN',
        'TXTOKENTXTOKENPAYPAL', 'TYPE_COUNTRY', 'UCAF_AUTHENTICATION_DATA',
        'UCAF_PAYMENT_CARD_CVC2', 'UCAF_PAYMENT_CARD_EXPDATE_MONTH',
        'UCAF_PAYMENT_CARD_EXPDATE_YEAR', 'UCAF_PAYMENT_CARD_NUMBER', 'USERID',
        'USERTYPE', 'VERSION', 'WBTU_MSISDN', 'WBTU_ORDERID', 'WEIGHTUNIT',
        'WIN3DS', 'WITHROOT'
    );

    protected static $allowedWildcard = array(
        'AIBOOKIND', 'AICARRIER', 'AICLASS', 'AIDESTCITY',
        'AIDESTCITYL', 'AIEXTRAPASNAME', 'AIFLDATE',
        'AIFLNUM', 'AIORCITY',
        'AIORCITYL', 'AISTOPOV', 'AITINUML', 'AMOUNT',
        'EXECUTIONDATE', 'FACEXCL',
        'FACTOTAL', 'ITEMATTRIBUTES', 'ITEMCATEGORY', 'ITEMCOMMENTS',
        'ITEMDESC', 'ITEMDISCOUNT', 'ITEMID', 'ITEMNAME',
        'ITEMPRICE', 'ITEMQUANT', 'ITEMQUANTORIG',
        'ITEMUNITOFMEASURE', 'ITEMVAT', 'ITEMVATCODE',
        'ITEMWEIGHT', 'LIDEXCL', 'MAXITEMQUANT', 'TAXINCLUDED',
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
        $shainString ='';

        // All parameters need to be arranged alphabetically.
        ksort($parameters);

        foreach ($parameters as $key => $value) {

            // Parameters that do not have a value should NOT be included in the string to hash
            if (empty($value)) {
                continue;
            }

            // All parameter names should be in UPPERCASE (to avoid any case confusion).
            $key = strtoupper($key);

            if (in_array($key, static::$allowed, true) || $this->isWildcarded($key)) {
                $shainString .= sprintf('%s=%s%s', $key, $value, $this->token->getShain());
            }
        }

        return strtoupper(sha1($shainString));
    }

    /**
     * @param string $key
     *
     * @return boolean
     */
    protected function isWildcarded($key)
    {
        foreach (static::$allowedWildcard as $allowed) {
            if (strpos($key, $allowed) !== false) {
                return true;
            }
        }

        return false;
    }
}
