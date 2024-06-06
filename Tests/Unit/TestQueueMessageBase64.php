<?php

namespace Unit;

use AliyunMNS\Requests\SendMessageRequest;
use AliyunMNS\Responses\ReceiveMessageResponse;

class TestQueueMessageBase64 extends \PHPUnit_Framework_TestCase
{
    public function testRawStringMessage()
    {
        $messageBody = 'test 字符串';
        // 发送时不进行 base64 编码
        $request = new SendMessageRequest($messageBody, NULL, NULL, FALSE);
        $xmlData = $request->generateBody();

        // 借助 ReceiveMessageResponse 对发送时生成的 xml 数据进行解析
        $response = new ReceiveMessageResponse();

        // 接收时不进行 base64 解码
        $response->setBase64(FALSE);
        $response->parseResponse(200, $xmlData);

        // 1.发送时不进行 base64 编码, 接收时不进行 base64 解码, 结果应为原字符串
        $this->assertEquals($messageBody, $response->getMessageBody());

        // 接收时进行 base64 解码
        $response->setBase64(TRUE);
        $response->parseResponse(200, $xmlData);

        // 2.发送时不进行 base64 编码, 接收时进行 base64 解码, 结果应为乱码, 与原字符串不相等
        $this->assertNotEquals($messageBody, $response->getMessageBody());
    }

    public function testBase64Message()
    {
        $messageBody = 'test 字符串';
        // 发送时进行 base64 编码
        $request = new SendMessageRequest($messageBody, NULL, NULL, TRUE);
        $xmlData = $request->generateBody();

        // 借助 ReceiveMessageResponse 对发送时生成的 xml 数据进行解析, 拿到原始的发送数据
        $response = new ReceiveMessageResponse();

        // 接收时不进行 base64 解码
        $response->setBase64(FALSE);
        $response->parseResponse(200, $xmlData);

        // 3.发送时进行 base64 编码, 接收时不进行 base64 解码, 结果应为 原字符串进行 base64 编码后的字符串
        $this->assertEquals(base64_encode($messageBody), $response->getMessageBody());

        // 接收时进行 base64 解码
        $response->setBase64(TRUE);
        $response->parseResponse(200, $xmlData);

        // 4.发送时进行 base64 编码, 接收时进行 base64 解码, 结果应为原字符串
        $this->assertEquals($messageBody, $response->getMessageBody());
    }

}
