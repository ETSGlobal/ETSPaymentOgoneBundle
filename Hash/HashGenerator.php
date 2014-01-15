<?php

namespace ETS\Payment\OgoneBundle\Hash;

interface HashGenerator
{
    public function generate(array $parameters);
}
