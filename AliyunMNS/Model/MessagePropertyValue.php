<?php

namespace AliyunMNS\Model;

class MessagePropertyValue
{
    private $dataType;
    private $binaryValue;
    private $stringValue;

    public function __construct($dataType, $binaryValue = null, $stringValue = null)
    {
        $this->dataType = $dataType;
        $this->binaryValue = $binaryValue;
        $this->stringValue = $stringValue;
    }

    public function getDataType()
    {
        return $this->dataType;
    }

    public function getBinaryValue()
    {
        return $this->binaryValue;
    }

    public function getStringValue()
    {
        return $this->stringValue;
    }

    public function writeXML(\XMLWriter $xmlWriter, $name)
    {
        $xmlWriter->startElement('PropertyValue');
        $xmlWriter->writeElement('Name', $name);
        
        if ($this->dataType === PropertyType::BINARY && $this->binaryValue !== null) {
            $xmlWriter->writeElement('Value', base64_encode($this->binaryValue));
        } else {
            $xmlWriter->writeElement('Value', $this->stringValue);
        }

        $xmlWriter->writeElement('Type', $this->dataType);
        $xmlWriter->endElement();
    }
}