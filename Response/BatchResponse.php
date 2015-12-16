<?php

namespace ETS\Payment\OgoneBundle\Response;

class BatchResponse extends DirectResponse
{
    /**
     * @param \SimpleXMLElement $xml
     */
    public function __construct(\SimpleXMLElement $xml)
    {
        parent::__construct($xml);
    }

    /**
     * @return integer
     */
    public function getStatusError()
    {
        return (int) $this->xml->FORMAT_CHECK[0]->FORMAT_CHECK_ERROR[0]->STATUS;
    }

    /**
     * @return string
     */
    public function getErrorCode()
    {
        $error = parent::getErrorCode();

        if ($this->hasError()) {
            $error = (string) $this->xml->FORMAT_CHECK[0]->FORMAT_CHECK_ERROR[0]->NCERROR;
        }

        return $error;
    }

    /**
     * @return string
     */
    public function getErrorDescription()
    {
        return (string) $this->xml->FORMAT_CHECK[0]->FORMAT_CHECK_ERROR[0]->ERROR;
    }

    /**
     * @return string
     */
    public function hasError()
    {
        return isset($this->xml->FORMAT_CHECK) && isset($this->xml->FORMAT_CHECK[0]->FORMAT_CHECK_ERROR);
    }
}
