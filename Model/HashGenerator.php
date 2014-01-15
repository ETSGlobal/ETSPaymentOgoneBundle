<?php

namespace ETS\Payment\OgoneBundle\Model;

interface HashGenerator
{
    public function generate(array $parameters);
}
