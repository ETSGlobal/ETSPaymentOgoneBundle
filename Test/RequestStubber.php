<?php

namespace ETS\Payment\OgoneBundle\Test;

class RequestStubber extends \PHPUnit_Framework_TestCase
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
     * @return Symfony\Component\HttpFoundation\Request
     */
    public function getStubbedRequest()
    {
        $requestStub = $this->getMock('Symfony\Component\HttpFoundation\Request', array('get'));

        $requestStub
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($this->map));

        $publicPropertyStub = $this->getMock('Symfony\Component\HttpFoundation\ParameterBag', array('all'));
        $publicPropertyStub
            ->expects($this->any())
            ->method('all')
            ->will($this->returnValue($this->getMapForParameterBags()));

        $requestStub->query   = $publicPropertyStub;
        $requestStub->request = clone $publicPropertyStub;

        return $requestStub;
    }

    /**
     * @param  boolean $withShasign
     * @return array
     */
    public function getMapForParameterBags($withShasign = true)
    {
        $mappedFields = array();

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
    public function getHashFromMap()
    {
        foreach ($this->map as $fieldMap) {
            if (in_array('SHASign', $fieldMap)) {
                return $fieldMap[3];
            }
        }

        return 'hash';
    }
}
