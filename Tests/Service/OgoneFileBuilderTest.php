<?php

namespace ETS\Payment\OgoneBundle\Tests\Response;

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

use ETS\Payment\OgoneBundle\Client\Token;
use ETS\Payment\OgoneBundle\Service\OgoneFileBuilder;
use InvalidArgumentException;

class OgoneFileBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testIsValidWithPayIdText()
    {
        $expectedTextContent = "OHL;ETSCPC;anglet64600;;userapi64600;
OHF;FILEorder_id25;MTR;SAS;1;
INV;EUR;;;;transactionId;LEGAL;;payId;SAS;;;;ETSCPC;;2;aliasGSP;azerty;;;1700065264;;;;;;;;order_id25;;;3000;616;3616;
CLI;LEGAL;;;;;;;;;;;;;;;;;;;
DET;1;id25;article25;1000;0;19.6%;;;;;;;1000;
DET;2;id26;article25;1000;0;21%;;;;;;;2000;
OTF;
";

        $token = new Token('ETSCPC', 'userapi64600', 'anglet64600', '', '');
        $builder = new OgoneFileBuilder($token);
        $articles = array(
            0 => array(
                'id' => 'id25',
                'quantity' => 1,
                'price' => 10,
                'name' => 'article25',
                'vat' => 0.196, //VAT: 196
            ),
            1 => array(
                'id' => 'id26',
                'quantity' => 2,
                'price' => 10,
                'name' => 'article25',
                'vat' => 0.21, //VAT: 420
            ),
        );
        $text = $builder->buildInv('order_id25', 'azerty', '1700065264', 'LEGAL', 'aliasGSP', 'SAS', $articles, 'payId', 'transactionId');

        $this->assertEquals($expectedTextContent, $text);
    }

    public function testIsValidWithNoPayIdText()
    {
        $expectedTextContent = "OHL;ETSCPC;anglet64600;;userapi64600;
OHF;FILEorder_id25;ATR;RES;1;
INV;EUR;;;;transactionId;LEGAL;;;RES;;;;ETSCPC;;2;aliasGSP;azerty;;;1700065264;;;;;;;;order_id25;;;3000;616;3616;
CLI;LEGAL;;;;;;;;;;;;;;;;;;;
DET;1;id25;article25;1000;0;19.6%;;;;;;;1000;
DET;2;id26;article25;1000;0;21%;;;;;;;2000;
OTF;
";

        $token = new Token('ETSCPC', 'userapi64600', 'anglet64600', '', '');
        $builder = new OgoneFileBuilder($token);
        $articles = array(
            0 => array(
                'id' => 'id25',
                'quantity' => 1,
                'price' => 10,
                'name' => 'article25',
                'vat' => 0.196, //VAT: 196
            ),
            1 => array(
                'id' => 'id26',
                'quantity' => 2,
                'price' => 10,
                'name' => 'article25',
                'vat' => 0.21, //VAT: 420
            ),
        );
        $text = $builder->buildInv('order_id25', 'azerty', '1700065264', 'LEGAL', 'aliasGSP', 'RES', $articles, '', 'transactionId');

        $this->assertEquals($expectedTextContent, $text);
    }

    public function provideInvalidArticles()
    {
        return array(
            array(
                array(
                    array(),
                ),
                array(
                    array('id' => 2),
                ),
                array(
                    array('id' => 2, 'quantity' => 1),
                ),
            ),
        );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidOperationCodeText()
    {
        $token = new Token('ETSCPC', 'userapi64600', 'anglet64600', '', '');
        $builder = new OgoneFileBuilder($token);
        $articles = array();
        $builder->buildInv('order_id25', 'azerty', '1700065264', 'LEGAL', 'aliasGSP', 'EE', $articles, 'payId');
    }

    /**
     * @dataProvider provideInvalidArticles
     * @expectedException InvalidArgumentException
     */
    public function testMissingKey(array $articles)
    {
        $token = new Token('ETSCPC', 'userapi64600', 'anglet64600', '', '');
        $builder = new OgoneFileBuilder($token);

        $builder->buildInv('order_id25', 'azerty', '1700065264', 'LEGAL', 'aliasGSP', 'RES', $articles, 'payId');
    }

    public function testValidRound()
    {
        $expectedTextContent = "OHL;ETSCPC;anglet64600;;userapi64600;
OHF;FILEorder_id25;MTR;SAS;1;
INV;EUR;;;;transactionId;LEGAL;;payId;SAS;;;;ETSCPC;;1;aliasGSP;azerty;;;1700065264;;;;;;;;order_id25;;;4403;881;5284;
CLI;LEGAL;;;;;;;;;;;;;;;;;;;
DET;1;id25;article25;4403;0;20%;;;;;;;4403;
OTF;
";

        $token   = new Token('ETSCPC', 'userapi64600', 'anglet64600', '', '');
        $builder = new OgoneFileBuilder($token);
        $article = array(
            0 => array(
                'id'       => 'id25',
                'quantity' => 1,
                'price'    => 44.03,
                'name'     => 'article25',
                'vat'      => 0.2,
            )
        );

        $result = $builder->buildInv('order_id25', 'azerty', '1700065264', 'LEGAL', 'aliasGSP', 'SAS', $article, 'payId', 'transactionId');

        $this->assertEquals($expectedTextContent, $result);
    }

    public function testValidRoundMultiArticles()
    {
        $expectedTextContent = "OHL;ETSCPC;anglet64600;;userapi64600;
OHF;FILEorder_id25;MTR;SAS;1;
INV;EUR;;;;transactionId;LEGAL;;payId;SAS;;;;ETSCPC;;2;aliasGSP;azerty;;;1700065264;;;;;;;;order_id25;;;5012;1002;6014;
CLI;LEGAL;;;;;;;;;;;;;;;;;;;
DET;1;id25;article25;4403;0;20%;;;;;;;4403;
DET;1;id26;article26;609;0;20%;;;;;;;609;
OTF;
";

        $token   = new Token('ETSCPC', 'userapi64600', 'anglet64600', '', '');
        $builder = new OgoneFileBuilder($token);
        $article = array(
            0 => array(
                'id'       => 'id25',
                'quantity' => 1,
                'price'    => 44.03,
                'name'     => 'article25',
                'vat'      => 0.2,
            ),
            1 => array(
                'id'       => 'id26',
                'quantity' => 1,
                'price'    => 6.09,
                'name'     => 'article26',
                'vat'      => 0.2,
            )
        );

        $result = $builder->buildInv('order_id25', 'azerty', '1700065264', 'LEGAL', 'aliasGSP', 'SAS', $article, 'payId', 'transactionId');

        $this->assertEquals($expectedTextContent, $result);
    }
}
