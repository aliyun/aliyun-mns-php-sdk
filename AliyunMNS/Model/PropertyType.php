<?php

namespace AliyunMNS\Model;

class PropertyType
{
    const NUMBER = 'NUMBER';
    const BOOLEAN = 'BOOLEAN';
    const STRING = 'STRING';
    const BINARY = 'BINARY';

    public static function isValid($type)
    {
        $validTypes = [self::NUMBER, self::BOOLEAN, self::STRING, self::BINARY];
        return in_array($type, $validTypes);
    }
}