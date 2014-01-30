<?php

namespace ETS\Payment\OgoneBundle\Exception;

use JMS\Payment\CoreBundle\Exception\Exception as CorePaymentException;

class NoPendingTransactionException extends CorePaymentException
{
}
