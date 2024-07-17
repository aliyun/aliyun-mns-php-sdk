<?php

namespace AliyunMNS\Requests;

class PublishBase64MessageRequest extends PublishMessageRequest
{
    public function __construct($messageBody, $messageTag = NULL, $messageAttributes = NULL)
    {
        parent::__construct(NULL, $messageTag, $messageAttributes);

        $this->messageBody = base64_encode($messageBody);
    }
}