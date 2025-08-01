<?php

namespace AliyunMNS\Traits;

use AliyunMNS\Constants;
use AliyunMNS\Model\MessagePropertyValue;
use AliyunMNS\Model\MessageSystemPropertyKey;
use AliyunMNS\Model\MessageSystemPropertyValue;
use AliyunMNS\Model\PropertyType;

trait MessageProperties
{
    private $userProperties = [];
    private $systemProperties = [];

    public function setUserProperties(array $userProperties)
    {
        foreach ($userProperties as $key => $value) {
            if (!($value instanceof MessagePropertyValue)) {
                throw new \InvalidArgumentException("Invalid Argument");
            }
        }

        $this->userProperties = $userProperties;
    }

    public function getUserProperties()
    {
        return $this->userProperties;
    }

    public function setSystemProperties(array $systemProperties)
    {
        foreach ($systemProperties as $key => $value) {
            if (!MessageSystemPropertyKey::isValid($key)) {
                throw new \InvalidArgumentException("Invalid system property key: {$key}");
            }

            if (!($value instanceof MessageSystemPropertyValue)) {
                throw new \InvalidArgumentException("System property value must be an instance of MessageSystemPropertyValue");
            }
        }

        $this->systemProperties = $systemProperties;
    }

    public function getSystemProperties()
    {
        return $this->systemProperties;
    }

    
    /**
     * Parses user properties from XML.
     */
    public static function parseUserPropertiesFromXML(\XMLReader $xmlReader)
    {
        if ($xmlReader->name !== Constants::USER_PROPERTIES_TAG) {
            return [];
        }

        $properties = [];

        while ($xmlReader->read()) {
            if ($xmlReader->nodeType === \XMLReader::ELEMENT && $xmlReader->name === Constants::MESSAGE_PROPERTY_TAG) {
                $name = null;
                $dataType = null;
                $stringValue = null;
                $binaryValue = null;

                while ($xmlReader->read() && !($xmlReader->nodeType === \XMLReader::END_ELEMENT && $xmlReader->name === Constants::MESSAGE_PROPERTY_TAG)) {
                    if ($xmlReader->nodeType === \XMLReader::ELEMENT) {
                        switch ($xmlReader->name) {
                            case Constants::PROPERTY_NAME_TAG:
                                $xmlReader->read();
                                if ($xmlReader->nodeType === \XMLReader::TEXT) {
                                    $name = $xmlReader->value;
                                }
                                break;
                            case Constants::PROPERTY_VALUE_TAG:
                                $xmlReader->read();
                                if ($xmlReader->nodeType === \XMLReader::TEXT) {
                                    $stringValue = $xmlReader->value;
                                }
                                break;
                            case Constants::PROPERTY_TYPE_TAG:
                                $xmlReader->read();
                                if ($xmlReader->nodeType === \XMLReader::TEXT) {
                                    $dataType = $xmlReader->value;
                                }
                                break;
                        }
                    }
                }

                if (!empty($name) && PropertyType::isValid($dataType)) {
                    if ($dataType === PropertyType::BINARY && $stringValue !== null) {
                        $binaryValue = base64_decode($stringValue);
                        $stringValue = null;
                    }

                    $properties[$name] = new MessagePropertyValue($dataType, $binaryValue, $stringValue);
                }
            }
        }
        return $properties;
    }

    /**
     * Parses system properties from XML.
     */
    public static function parseSystemPropertiesFromXML(\XMLReader $xmlReader)
    {
        if ($xmlReader->name !== Constants::SYSTEM_PROPERTIES_TAG) {
            return [];
        }

        $properties = [];

        while ($xmlReader->read() && !($xmlReader->nodeType === \XMLReader::END_ELEMENT && $xmlReader->name === Constants::SYSTEM_PROPERTIES_TAG)) {
            if ($xmlReader->nodeType === \XMLReader::ELEMENT && $xmlReader->name === Constants::MESSAGE_SYSTEM_PROPERTY_TAG) {
                $name = null;
                $dataType = null;
                $stringValue = null;

                while ($xmlReader->read() && !($xmlReader->nodeType === \XMLReader::END_ELEMENT && $xmlReader->name === Constants::MESSAGE_SYSTEM_PROPERTY_TAG)) {
                    switch ($xmlReader->name) {
                        case Constants::PROPERTY_NAME_TAG:
                            $xmlReader->read();
                            if ($xmlReader->nodeType === \XMLReader::TEXT) {
                                $name = $xmlReader->value;
                            }
                            break;
                        case Constants::PROPERTY_VALUE_TAG:
                            $xmlReader->read();
                            if ($xmlReader->nodeType === \XMLReader::TEXT) {
                                $stringValue = $xmlReader->value;
                            }
                            break;
                        case Constants::PROPERTY_TYPE_TAG:
                            $xmlReader->read();
                            if ($xmlReader->nodeType === \XMLReader::TEXT) {
                                $dataType = $xmlReader->value;
                            }
                            break;
                    }
                }

                if (!empty($name) && PropertyType::isValidSys($dataType)) {
                    $properties[$name] = new MessageSystemPropertyValue($dataType, $stringValue);
                }
            }
        }

        return $properties;
    }
}