<?php

namespace ETS\Payment\OgoneBundle\Service;


use ETS\Payment\OgoneBundle\Client\TokenInterface;
use ETS\Payment\OgoneBundle\Plugin\OgoneBatchGatewayPlugin;

class OgoneFileBuilder
{
    const INV_FILE_LENGTH = 34;
    const INV_DET_FILE_LENGTH = 14;

    private $pspId;
    private $apiUser;
    private $apiPassword;

    public function __construct(TokenInterface $token)
    {
        $this->pspId = $token->getPspid();
        $this->apiUser = $token->getApiUser();
        $this->apiPassword = $token->getApiPassword();
    }

    /**
     * @param string $orderId
     * @param string $clientRef
     * @param string $clientId
     * @param string $aliasId
     * @param string $operation
     * @param array  $articles
     * @param string $payId
     *
     * @return string
     */
    public function buildInv($orderId, $clientId, $clientRef, $aliasId, $operation, array $articles, $payId = '')
    {
        $this->validateOperation($operation);
        $transaction = (OgoneBatchGatewayPlugin::AUTHORIZATION === $operation) ? OgoneBatchGatewayPlugin::TRANSACTION_CODE_NEW : OgoneBatchGatewayPlugin::TRANSACTION_CODE_MAINTENANCE;

        $globalInformationLine = $this->createGlobalInformationLineArray();
        $globalOperationLine = $this->createGlobalOperationLineArray($orderId, $transaction, $operation);

        $amountTaxExcluded  = 0;
        $amountValueAddedTax = 0;
        $nbArticles = 0;
        $articlesLines = array();
        foreach($articles as $k => $article) {
            $id        = $article['id'];
            $quantity  = $article['quantity'];
            $unitPrice = $article['price'] * 100;
            $name      = substr($article['name'], 0, 39);
            $vat       = $article['vat'];
            $price     = $quantity * $unitPrice;
            $articlesLines[$k] = $this->createDetailLineArray($quantity, $id, $name, $unitPrice, $vat, $price);
            $amountTaxExcluded += $price; //tax excluded
            $amountValueAddedTax += $price * $vat;
            $nbArticles++;
        }

        $amountTaxIncluded = $amountTaxExcluded + $amountValueAddedTax; //amount taxes included

        $globalSummaryLine = $this->createGlobalSummaryLineArray($orderId, $payId, $operation, $nbArticles, $aliasId, $clientId, $clientRef, $amountTaxExcluded, $amountValueAddedTax, $amountTaxIncluded);

        $globalEndOfFileLine = $this->createEndOfFileLineArray();

        $lines = array();

        $lines[] = $globalInformationLine;
        $lines[] = $globalOperationLine;
        $lines[] = $globalSummaryLine;
        foreach ($articlesLines as $articlesLine) {
            $lines[] = $articlesLine;
        }
        $lines[] = $globalEndOfFileLine;

        return $this->buildText($lines);
    }

    /**
     * @param string $orderId
     * @param string $payId
     * @param string $operation
     * @param string $nbArticles
     * @param string $aliasId
     * @param string $clientId
     * @param string $clientRef
     * @param string $amountTaxExcluded
     * @param string $amountVat
     * @param string $amountTaxIncluded
     *
     * @return array
     */
    private function createGlobalSummaryLineArray($orderId, $payId, $operation, $nbArticles, $aliasId, $clientId, $clientRef, $amountTaxExcluded, $amountVat, $amountTaxIncluded)
    {
        $globalSummaryLine = $this->initArray(self::INV_FILE_LENGTH);
        $globalSummaryLine[0] = 'INV';
        $globalSummaryLine[1] = 'EUR';
        $globalSummaryLine[5] = $orderId;
        $globalSummaryLine[6] = $clientRef;
        $globalSummaryLine[8] = $payId;
        $globalSummaryLine[9] = $operation;
        $globalSummaryLine[13] = $this->pspId;
        $globalSummaryLine[15] = $nbArticles;
        $globalSummaryLine[16] = $aliasId;
        $globalSummaryLine[17] = $clientId;
        $globalSummaryLine[28] = $orderId;
        $globalSummaryLine[30] = $clientRef;
        $globalSummaryLine[31] = $amountTaxExcluded;
        $globalSummaryLine[32] = $amountVat;
        $globalSummaryLine[33] = $amountTaxIncluded;

        return $globalSummaryLine;
    }

    /**
     * @return array
     */
    private function createGlobalInformationLineArray()
    {
        $globalInformationLine = $this->initArray(4);
        $globalInformationLine[0] = 'OHL';
        $globalInformationLine[1] = $this->pspId;
        $globalInformationLine[2] = $this->apiPassword;
        $globalInformationLine[4] = $this->apiUser;

        return $globalInformationLine;
    }

    /**
     * @param $orderId
     * @param $transaction
     * @param $operation
     * @return array
     */
    private function createGlobalOperationLineArray($orderId, $transaction, $operation)
    {
        $globalOperationLine = $this->initArray(5);
        $globalOperationLine[0] = 'OHF';
        $globalOperationLine[1] = 'FILE'.$orderId;
        $globalOperationLine[2] = $transaction;
        $globalOperationLine[3] = $operation;
        $globalOperationLine[4] = '1';

        return $globalOperationLine;
    }

    /**
     * @param $quantity
     * @param $id
     * @param $name
     * @param $unitPrice
     * @param $vat
     * @param $price
     * @return array
     */
    private function createDetailLineArray($quantity, $id, $name, $unitPrice, $vat, $price)
    {
        $articlesLines = $this->initArray(self::INV_DET_FILE_LENGTH);
        $articlesLines[0] = 'DET';
        $articlesLines[1] = $quantity;
        $articlesLines[2] = $id;
        $articlesLines[3] = $name;
        $articlesLines[4] = $unitPrice;
        $articlesLines[5] = 0;
        $articlesLines[6] = ($vat * 100).'%';
        $articlesLines[13] = $price;

        return $articlesLines;
    }

    /**
     * @return array
     */
    private function createEndOfFileLineArray()
    {
        $globalEndOfFileLine = $this->initArray(1);
        $globalEndOfFileLine[0] = 'OTF';

        return $globalEndOfFileLine;
    }

    /**
     * @param $operation
     */
    private function validateOperation($operation)
    {
        if (!in_array($operation, OgoneBatchGatewayPlugin::getAvailableOperations())) {
            throw new \InvalidArgumentException('Invalid parameter "operation"');
        }
    }

    /**
     * @param $size
     * @return array
     */
    private function initArray($size)
    {
        $array = array();
        for ($i = 0; $i < $size; $i++) {
            $array[$i] = '';
        }

        return $array;
    }

    /**
     * @param array $lines
     * @return string
     */
    private function buildText(array $lines)
    {
        $file = '';

        foreach ($lines as $line) {
            foreach($line as $value) {
                $file.=$value.';';
            }
            $file.="\n";
        }

        return $file;
    }
}
