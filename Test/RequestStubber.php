<?php

namespace ETS\Payment\OgoneBundle\Test;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class RequestStubber extends TestCase
{
    /**
     * @var array
     */
    private $map;

    /**
     * @param array $map
     */
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    /**
     * @return Request
     */
    public function getStubbedRequest(): Request
    {
        return new Request($this->getMapForParameterBags(), $this->getMapForParameterBags());
    }

    /**
     * @param  boolean $withShasign
     *
     * @return array
     */
    public function getMapForParameterBags($withShasign = true): array
    {
        $mappedFields = [];

        foreach ($this->map as $fieldMap) {
            if (false === $withShasign && 'SHASign' === $fieldMap[0]) {
                continue;
            }

            $mappedFields[$fieldMap[0]] = $fieldMap[3];
        }

        return $mappedFields;
    }

    /**
     * @return string
     */
    public function getHashFromMap(): string
    {
        foreach ($this->map as $fieldMap) {
            if (in_array('SHASign', $fieldMap, true)) {
                return $fieldMap[3];
            }
        }

        return 'hash';
    }
}
