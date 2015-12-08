<?php

namespace ETS\Payment\OgoneBundle\Plugin;

use JMS\Payment\CoreBundle\Plugin\GatewayPlugin;

abstract class OgoneGatewayBasePlugin extends GatewayPlugin
{
    /**
     * @var bool
     */
    protected $utf8;

    public function __construct($isDebug, $utf8 = false)
    {
        $this->utf8 = $utf8;
        parent::__construct($isDebug);
    }

    /**
     * @return string
     */
    protected function getStandardOrderUrl()
    {
        return sprintf(
            'https://secure.ogone.com/ncol/%s/orderstandard%s.asp',
            $this->debug ? 'test' : 'prod',
            $this->utf8 ? '_utf8' : ''
        );
    }

    /**
     * @return string
     */
    protected function getDirectQueryUrl()
    {
        return sprintf(
            'https://secure.ogone.com/ncol/%s/querydirect%s.asp',
            $this->debug ? 'test' : 'prod',
            $this->utf8 ? '_utf8' : ''
        );
    }

    /**
     * @return string
     */
    protected function getBatchUrl()
    {
        return sprintf(
            'https://secure.ogone.com/ncol/%s/AFU_agree.asp',
            $this->debug ? 'test' : 'prod'
        );
    }
}
