<?php

namespace Unit;

require_once __DIR__ . "/../Compatibility.php";

use AliyunMNS\Requests\PublishBase64MessageRequest;
use AliyunMNS\Requests\PublishMessageRequest;

class TopicMessageBase64Test extends \PHPUnit_Framework_TestCase
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
