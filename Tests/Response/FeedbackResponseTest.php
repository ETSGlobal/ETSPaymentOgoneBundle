<?php

declare(strict_types=1);

namespace ETS\Payment\OgoneBundle\Tests\Response;

use ETS\Payment\OgoneBundle\Response\FeedbackResponse;
use ETS\Payment\OgoneBundle\Test\RequestStubber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;

class FeedbackResponseTest extends TestCase
{
    private RequestStubber $requestStubber;

    private RequestStack $requestStack;

    public function setUp(): void
    {
        $this->requestStubber = new RequestStubber([
            ['orderID', null, false, 42],
            ['amount', null, false, '42'],
            ['currency', null, false, 'EUR'],
            ['PM', null, false, 'credit card'],
            ['STATUS', null, false, 5],
            ['CARDNO', null, false, 4567123478941234],
            ['PAYID', null, false, 43],
            ['SHASign', null, false, 'fzgzgzghz4648zh6z5h'],
        ]);

        $this->requestStack = new RequestStack();
    }

    public function testAddValueFieldAlreadySetEvenIfDifferentCase(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('already set');

        $this->requestStack->push($this->requestStubber->getStubbedRequest());
        $feedbackResponse = new FeedbackResponse($this->requestStack);

        $class = new \ReflectionClass($feedbackResponse);
        $addValueMethod = $class->getMethod('addValue');
        $addValueMethod->setAccessible(true);

        $addValueMethod->invokeArgs($feedbackResponse, ['ORDERid', 48]);
    }

    public function testGetValueUnsetField(): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->expectExceptionMessage('was not sent with the Request');

        $this->requestStack->push($this->requestStubber->getStubbedRequest());
        $feedbackResponse = new FeedbackResponse($this->requestStack);

        $class = new \ReflectionClass($feedbackResponse);
        $getValueMethod = $class->getMethod('getValue');
        $getValueMethod->setAccessible(true);

        $getValueMethod->invokeArgs($feedbackResponse, array('dummyField'));
    }

    public function testConstructor(): void
    {
        $this->requestStack->push($this->requestStubber->getStubbedRequest());
        $feedbackResponse = new FeedbackResponse($this->requestStack);

        $this->assertSame($this->requestStubber->getMapForParameterBags(false), $feedbackResponse->getValues());
        $this->assertSame($this->requestStubber->getHashFromMap(), $feedbackResponse->getHash());
    }
}
