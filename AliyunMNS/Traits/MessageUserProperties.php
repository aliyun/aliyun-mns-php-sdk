<?php

namespace AliyunMNS\Traits;

use AliyunMNS\Model\MessagePropertyValue;
use AliyunMNS\Model\PropertyType;

trait MessageUserProperties
{
    private $userProperties = [];

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

   public function writeXMLForUserProperties(\XMLWriter $xmlWriter)
    {
        if (!empty($this->userProperties)) {
            $xmlWriter->startElement('UserProperties');
            foreach ($this->userProperties as $name => $propertyValue) {
                $propertyValue->writeXML($xmlWriter, $name);
            }
            $xmlWriter->endElement(); // UserProperties
        }
    }

        /**
     * Parses user properties from XML.
     */
    public static function parseUserPropertiesFromXML(\XMLReader $xmlReader)
    {
        if ($xmlReader->name !== 'UserProperties') {
            return [];
        }

        $properties = [];

        while ($xmlReader->read()) {
            if ($xmlReader->nodeType === \XMLReader::ELEMENT && $xmlReader->name === 'PropertyValue') {
                $name = null;
                $dataType = null;
                $stringValue = null;
                $binaryValue = null;

                while ($xmlReader->read() && !($xmlReader->nodeType === \XMLReader::END_ELEMENT && $xmlReader->name === 'PropertyValue')) {
                    if ($xmlReader->nodeType === \XMLReader::ELEMENT) {
                        switch ($xmlReader->name) {
                            case 'Name':
                                $xmlReader->read();
                                if ($xmlReader->nodeType === \XMLReader::TEXT) {
                                    $name = $xmlReader->value;
                                }
                                break;
                            case 'Value':
                                $xmlReader->read();
                                if ($xmlReader->nodeType === \XMLReader::TEXT) {
                                    $stringValue = $xmlReader->value;
                                }
                                break;
                            case 'Type':
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
}