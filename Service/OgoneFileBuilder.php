<?php

namespace ETS\Payment\OgoneBundle\Service;


use ETS\Payment\OgoneBundle\Client\TokenInterface;
use ETS\Payment\OgoneBundle\Plugin\OgoneBatchGatewayPlugin;

/**
 * To build the batch file that will be sent to Ogone
 */
class OgoneFileBuilder
{
    const INV_FILE_LENGTH = 34;
    const INV_DET_FILE_LENGTH = 14;

    /**
     * @var TokenInterface
     */
    private $token;

    /**
     *  @param TokenInterface $token
     */
    public function __construct(TokenInterface $token)
    {
        $this->token = $token;
    }

    /**
     * @param string $orderId
     * @param string $clientId
     * @param string $clientRef
     * @param string $legalCommitment
     * @param string $aliasId
     * @param string $operation
     * @param array  $articles
     * @param string $payId
     * @param string $transactionId
     *
     * @return string
     */
    public function buildInv($orderId, $clientId, $clientRef, $legalCommitment, $aliasId, $operation, array $articles, $payId = '', $transactionId = '')
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
            $this->validateArticle($article);
            $id        = $article['id'];
            $quantity  = $article['quantity'];
            $unitPrice = round($article['price'], 2) * 100;
            $name      = substr($article['name'], 0, 39);
            $vat       = $article['vat'];
            $price     = $quantity * $unitPrice;
            $articlesLines[$k] = $this->createDetailLineArray($quantity, $id, $name, $unitPrice, $vat, $price);
            $amountTaxExcluded += $price; //tax excluded
            $amountValueAddedTax += round($price * $vat);
            $nbArticles++;
        }

        $amountTaxIncluded = $amountTaxExcluded + $amountValueAddedTax; //amount taxes included

        $globalSummaryLine = $this->createGlobalSummaryLineArray(
            $orderId,
            $payId,
            $operation,
            $nbArticles,
            $aliasId,
            $clientId,
            $clientRef,
            $amountTaxExcluded,
            $amountValueAddedTax,
            $amountTaxIncluded,
            $transactionId,
            $legalCommitment
        );

        $globalEndOfFileLine = $this->createEndOfFileLineArray();

        $globalClientFileLine = $this->createGlobalClientFileArray($legalCommitment);

        $lines = array();

        $lines[] = $globalInformationLine;
        $lines[] = $globalOperationLine;
        $lines[] = $globalSummaryLine;
        $lines[] = $globalClientFileLine;
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
     * @param string $transactionId
     * @param string $legalCommitment
     *
     * @return array
     */
    private function createGlobalSummaryLineArray($orderId, $payId, $operation, $nbArticles, $aliasId, $clientId, $clientRef, $amountTaxExcluded, $amountVat, $amountTaxIncluded, $transactionId, $legalCommitment)
    {
        $globalSummaryLine = $this->initArray(self::INV_FILE_LENGTH);
        $globalSummaryLine[0] = 'INV';
        $globalSummaryLine[1] = 'EUR';
        $globalSummaryLine[5] = $transactionId;
        $globalSummaryLine[6] = $legalCommitment;
        $globalSummaryLine[8] = $payId;
        $globalSummaryLine[9] = $operation;
        $globalSummaryLine[13] = $this->token->getPspid();
        $globalSummaryLine[15] = $nbArticles;
        $globalSummaryLine[16] = $aliasId;
        $globalSummaryLine[17] = $clientId;
        $globalSummaryLine[20] = $clientRef;
        $globalSummaryLine[28] = $orderId;
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
        $globalInformationLine[1] = $this->token->getPspid();
        $globalInformationLine[2] = $this->token->getApiPassword();
        $globalInformationLine[4] = $this->token->getApiUser();

        return $globalInformationLine;
    }


    /**
     * @param string $legalCommitment
     *
     * @return array
     */
    private function createGlobalClientFileArray($legalCommitment)
    {
        $clientLine = $this->initArray(20);
        $clientLine[0] = 'CLI';
        $clientLine[1] = $legalCommitment;

        return $clientLine;
    }

    /**
     * @param string $orderId
     * @param string $transaction
     * @param string $operation
     *
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
     * @param string $quantity
     * @param string $id
     * @param string $name
     * @param string $unitPrice
     * @param string $vat
     * @param string $price
     *
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
     * @param string $operation
     *
     * @throws \InvalidArgumentException
     */
    private function validateOperation($operation)
    {
        if (!in_array($operation, OgoneBatchGatewayPlugin::getAvailableOperations())) {
            throw new \InvalidArgumentException('Invalid parameter "operation"');
        }
    }

    /**
     * @param array $article
     */
    private function validateArticle(array $article)
    {
        $keys = array('id', 'quantity', 'price', 'name', 'vat');
        foreach ($keys as $key) {
            if (!isset($article[$key])) {
                throw new \InvalidArgumentException("Parameter $key is missing");
            }
        }
    }

    /**
     * @param string $size
     *
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
     *
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
