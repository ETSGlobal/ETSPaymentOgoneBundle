<?php

namespace ETS\Payment\OgoneBundle\Response;

class BatchResponse extends DirectResponse
{
    /**
     * @return integer
     */
    public function getStatusError()
    {
        $statusError = parent::getStatus();

        if ($this->hasErrorDetails()) {
            $statusError = (int) $this->xml->FORMAT_CHECK[0]->FORMAT_CHECK_ERROR[0]->STATUS;
        }

        return $statusError;
    }

    /**
     * @return string
     */
    public function getErrorCode()
    {
        $error = parent::getErrorCode();

        if ($this->hasErrorDetails()) {
            $error = (string) $this->xml->FORMAT_CHECK[0]->FORMAT_CHECK_ERROR[0]->NCERROR;
        }

        return $error;
    }

    /**
     * @return string
     */
    public function getErrorDescription()
    {
        $errorDescription = parent::getErrorDescription();

        if ($this->hasErrorDetails()) {
            $errorDescription = (string) $this->xml->FORMAT_CHECK[0]->FORMAT_CHECK_ERROR[0]->ERROR;
        }

        return $errorDescription;
    }

    /**
     * @return bool
     */
    private function hasErrorDetails()
    {
        return isset($this->xml->FORMAT_CHECK) && isset($this->xml->FORMAT_CHECK[0]->FORMAT_CHECK_ERROR);
    }

    /**
     * @return bool
     */
    public function hasError()
    {
        return parent::hasError() || $this->hasErrorDetails();
    }
}
