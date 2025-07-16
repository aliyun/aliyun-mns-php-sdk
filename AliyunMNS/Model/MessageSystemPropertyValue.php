<?php

namespace AliyunMNS\Model;

use AliyunMNS\Constants;

class MessageSystemPropertyValue
{
    private $dataType;
    private $stringValue;

    public function __construct($dataType, $stringValue)
    {
        $this->dataType = $dataType;
        $this->stringValue = $stringValue;
    }

    public function getDataType()
    {
        return $this->dataType;
    }

    public function getStringValue()
    {
        return $this->stringValue;
    }

    public function writeXML(\XMLWriter $xmlWriter, $name)
    {
        $xmlWriter->startElement(Constants::MESSAGE_SYSTEM_PROPERTY_TAG);
        $xmlWriter->writeElement(Constants::PROPERTY_NAME_TAG, $name);

        if ($this->dataType === PropertyType::STRING) {
            $xmlWriter->writeElement(Constants::PROPERTY_VALUE_TAG, $this->stringValue);
        }

        $xmlWriter->writeElement(Constants::PROPERTY_TYPE_TAG, $this->dataType);
        $xmlWriter->endElement();
    }
}