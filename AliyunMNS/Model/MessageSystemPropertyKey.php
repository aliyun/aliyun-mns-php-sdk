<?php

namespace AliyunMNS\Model;

class MessageSystemPropertyKey
{
    const TRACE_PARENT = 'traceparent';
    const TRACE_STATE = 'tracestate';
    const BAGGAGE = 'baggage';
    const DLQ_MESSAGE_TYPE = 'DLQMessageType';
    const DLQ_SOURCE_ARN = 'DLQSourceArn';
    const DLQ_ORIGIN_MESSAGE_ID = 'DLQOriginMessageId';

    public static function isValid($key)
    {
        $validKeys = [
            self::TRACE_PARENT,
            self::TRACE_STATE,
            self::BAGGAGE,
            self::DLQ_MESSAGE_TYPE,
            self::DLQ_SOURCE_ARN,
            self::DLQ_ORIGIN_MESSAGE_ID
        ];
        return in_array($key, $validKeys);
    }
}