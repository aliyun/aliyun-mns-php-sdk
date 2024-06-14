<?php

namespace Unit;

use AliyunMNS\Requests\PublishBase64MessageRequest;
use AliyunMNS\Requests\PublishMessageRequest;

if (!class_exists('\PHPUnit_Framework_TestCase') && class_exists('\PHPUnit\Framework\TestCase'))
    class_alias('\PHPUnit\Framework\TestCase', '\PHPUnit_Framework_TestCase');

class TestTopicMessageBase64 extends \PHPUnit_Framework_TestCase
{
    public function testRawStringMessage()
    {
        $messageBody = 'test 字符串';
        $request = new PublishMessageRequest($messageBody);

        $this->assertEquals($messageBody, $request->getMessageBody());
    }

    public function testBase64Message()
    {
        $messageBody = 'test 字符串';
        $request = new PublishBase64MessageRequest($messageBody);

        $this->assertEquals(base64_encode($messageBody), $request->getMessageBody());
    }

}
