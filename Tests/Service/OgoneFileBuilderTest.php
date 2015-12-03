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

class OgoneFileBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testIsValidText()
    {
        $expectedTextContent = "OHL;ETSCPC;anglet64600;;userapi64600;
OHF;FILEorder_id25;MTR;SAS;1;
INV;EUR;;;;order_id25;1700065264;;payId;SAS;;;;ETSCPC;;2;aliasGSP;azerty;;;;;;;;;;;order_id25;;1700065264;3000;58800;61800;
DET;1;id25;article25;1000;0;19.6%;;;;;;;1000;
DET;2;id26;article25;1000;0;19.6%;;;;;;;2000;
OTF;
";

        $token = new Token('ETSCPC', 'userapi64600', 'anglet64600', '', '', '1700065264');
        $builder = new OgoneFileBuilder($token);
        $articles = array(
            0 => array(
                'id' => 'id25',
                'quantity' => 1,
                'price' => 10,
                'name' => 'article25',
                'vat' => 19.6,
            ),
            1 => array(
                'id' => 'id26',
                'quantity' => 2,
                'price' => 10,
                'name' => 'article25',
                'vat' => 19.6,
            ),
        );
        $text = $builder->buildInv('order_id25', 'azerty', 'aliasGSP', 'SAS', 19.6, $articles, 'payId');

        $this->assertEquals($expectedTextContent, $text);
    }
}
