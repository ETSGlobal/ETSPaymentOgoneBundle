<?php

namespace ETS\Payment\OgoneBundle\Response;

class FeedbackResponse extends AbstractResponse
{
    private static $fields = array(
        'orderID',
        'amount',
        'currency',
        'PM',
        'ACCEPTANCE',
        'STATUS',
        'CARDNO',
        'PAYID',
        'NCERROR',
        'BRAND',
        'SHASIGN',
    );

    private $values = array();
    private $hash;

    public function __construct(array $feedback)
    {
        foreach ($feedback as $field => $value) {
            $this->addValue($field, $value);
        }
    }

    /**
     * get the list of fields that could be sent by Ogone
     * @return array
     */
    public static function getFields()
    {
        return self::$fields;
    }

    public function getValues()
    {
        return $this->values;
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function getOrderId()
    {
        return $this->getValue('orderID');
    }

    public function getPaymentId()
    {
        return $this->getValue('PAYID');
    }

    public function getAmount()
    {
        return $this->getValue('amount');
    }

    public function getStatus()
    {
        return $this->getValue('status');
    }

    public function getErrorCode()
    {
        return $this->getValue('NCERROR');
    }

    public function getErrorDescription()
    {
        return $this->getValue('NCERRORPLUS');
    }

    /**
     * [addValue description]
     * @param string $field
     * @param mixed $value
     *
     * @throws \BadMethodCallException If we try to set the value of a field twice
     */
    private function addValue($field, $value)
    {
        $field = strtoupper($field);

        if (isset($this->values[$field])) {
            throw new \BadMethodCallException(sprintf('Feedback parameter [%s] already set.', $field));
        }

        if ('SHASIGN' !== $field) {
            $this->values[$field] = $value;
        } else {
            $this->hash = $value;
        }
    }

    /**
     * @param  string $field
     * @return string|integer
     *
     * @throws \OutOfRangeException If the given field has not been set when this object has been instanciated
     */
    private function getValue($field)
    {
        $field = strtoupper($field);

        if (!isset($this->values[$field])) {
            throw new \OutOfRangeException(sprintf('Feedback parameter [%s] was not sent with the Request.', $field));
        }

        return $this->values[$field];
    }
}
