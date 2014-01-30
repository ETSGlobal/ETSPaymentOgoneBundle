<?php

namespace ETS\Payment\OgoneBundle\Tests\Response;

use ETS\Payment\OgoneBundle\Response\FeedbackResponse;
use ETS\Payment\OgoneBundle\Test\RequestStubber;

class FeedbackResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ETS\Payment\OgoneBundle\Test\RequestStubber
     */
    private $requestStubber;

    public function setUp()
    {
        $this->requestStubber = new RequestStubber(array(
            array('orderID', null, false, 42),
            array('amount', null, false, '42'),
            array('currency', null, false, 'EUR'),
            array('PM', null, false, 'credit card'),
            array('STATUS', null, false, 5),
            array('CARDNO', null, false, 4567123478941234),
            array('PAYID', null, false, 43),
            array('SHASign', null, false, 'fzgzgzghz4648zh6z5h')
        ));
    }

    /**
     * @expectedException        \BadMethodCallException
     * @expectedExceptionMessage already set
     */
    public function testAddValueFieldAlreadySetEvenIfDifferentCase()
    {
        $feedbackResponse = new FeedbackResponse($this->requestStubber->getStubbedRequest());

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
        $feedbackResponse = new FeedbackResponse($this->requestStubber->getStubbedRequest());

        $class = new \ReflectionClass($feedbackResponse);
        $getValueMethod = $class->getMethod('getValue');
        $getValueMethod->setAccessible(true);

        $getValueMethod->invokeArgs($feedbackResponse, array('dummyField'));
    }

    public function testConstructor()
    {
        $feedbackResponse = new FeedbackResponse($this->requestStubber->getStubbedRequest());

        $this->assertSame($this->requestStubber->getMapForParameterBags(false), $feedbackResponse->getValues());
        $this->assertSame($this->requestStubber->getHashFromMap(), $feedbackResponse->getHash());
    }
}
