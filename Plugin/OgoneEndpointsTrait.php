<?php

namespace ETS\Payment\OgoneBundle\Plugin;

trait OgoneEndpointsTrait
{
    protected $debug;

    protected $utf8;

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