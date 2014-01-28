<?php

namespace ETS\Payment\OgoneBundle\Tests\Response;

use ETS\Payment\OgoneBundle\Hash\Sha1Out;
use ETS\Payment\OgoneBundle\Response\FeedbackResponse;

class FeedbackResponseTest extends \PHPUnit_Framework_TestCase
{
    private $map = array(
        array('orderID', null, false, 42),
        array('amount', null, false, '42'),
        array('currency', null, false, 'EUR'),
        array('PM', null, false, 'credit card'),
        array('STATUS', null, false, 5),
        array('CARDNO', null, false, 4567123478941234),
        array('PAYID', null, false, 43),
        array('SHASign', null, false, 'fzgzgzghz4648zh6z5h')
    );

    /**
     * @expectedException        \BadMethodCallException
     * @expectedExceptionMessage already set
     */
    public function testAddValueFieldAlreadySetEvenIfDifferentCase()
    {
        $feedbackResponse = $this->getFeedbackResponseWithStubbedRequest();

        $class = new \ReflectionClass($feedbackResponse);
        $addValueMethod = $class->getMethod('addValue');
        $addValueMethod->setAccessible(true);

        $addValueMethod->invokeArgs($feedbackResponse, array('ORDERid', 48));
    }

    /**
     * @expectedException        \OutOfRangeException
     * @expectedExceptionMessage was not sent with the Request
     */
    public function testGetValueUnsetField()
    {
        $feedbackResponse = $this->getFeedbackResponseWithStubbedRequest();

        $class = new \ReflectionClass($feedbackResponse);
        $getValueMethod = $class->getMethod('getValue');
        $getValueMethod->setAccessible(true);

        $getValueMethod->invokeArgs($feedbackResponse, array('dummyField'));
    }

    public function testConstructor()
    {
        $feedbackResponse = $this->getFeedbackResponseWithStubbedRequest();

        $this->assertSame($this->getMapForParameterBags(false), $feedbackResponse->getValues());
        $this->assertSame($this->getHashFromMap(), $feedbackResponse->getHash());
    }

    private function getFeedbackResponseWithStubbedRequest()
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

        return new FeedbackResponse($requestStub);
    }

    private function getMapForParameterBags($withShasign = true)
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

    private function getHashFromMap()
    {
        foreach ($this->map as $fieldMap) {
            if (in_array('SHASign', $fieldMap)) {
                return $fieldMap[3];
            }
        }

        return 'hash';
    }
}
