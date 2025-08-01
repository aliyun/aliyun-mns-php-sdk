<?php

namespace AliyunMNS\Model;

use AliyunMNS\Constants;

class MessagePropertyValue
{
    private $dataType;
    private $binaryValue;
    private $stringValue;

    public function __construct($dataType, $binaryValue = null, $stringValue = null)
    {
        if ($dataType === null) {
            throw new \InvalidArgumentException("type and value can not be null");
        }
        
        $this->dataType = $dataType;
        
        switch ($dataType) {
            case PropertyType::NUMBER:
                if ($stringValue === null) {
                    throw new \InvalidArgumentException("type and value can not be null");
                }
                
                // 校验是否是数字
                if (!is_numeric($stringValue)) {
                    throw new \InvalidArgumentException("Invalid number format: " . $stringValue);
                }
                $this->stringValue = $stringValue;
                break;
                
            case PropertyType::STRING:
                if ($stringValue === null) {
                    throw new \InvalidArgumentException("type and value can not be null");
                }
                $this->stringValue = $stringValue;
                break;
                
            case PropertyType::BOOLEAN:
                if ($stringValue === null) {
                    throw new \InvalidArgumentException("type and value can not be null");
                }
                
                // 校验是否为合法的布尔值
                if (is_bool($stringValue)) {
                    $this->stringValue = $stringValue ? "true" : "false";
                } elseif (is_string($stringValue)) {
                    $lowerValue = strtolower($stringValue);
                    if ($lowerValue === "true" || $lowerValue === "false") {
                        $this->stringValue = $lowerValue;
                    } else {
                        throw new \InvalidArgumentException("Invalid boolean value: " . $stringValue);
                    }
                } elseif (is_int($stringValue)) {
                    // 处理整数形式的布尔值 (1 或 0)
                    $this->stringValue = $stringValue ? "true" : "false";
                } else {
                    throw new \InvalidArgumentException("Invalid boolean value: " . $stringValue);
                }
                break;
                
            case PropertyType::BINARY:
                if ($binaryValue === null) {
                    throw new \InvalidArgumentException("type and value can not be null");
                }
                $this->binaryValue = $binaryValue;
                break;
                
            default:
                throw new \InvalidArgumentException("Invalid property type: " . $dataType);
        }
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
        $xmlWriter->startElement(Constants::MESSAGE_PROPERTY_TAG);
        $xmlWriter->writeElement(Constants::PROPERTY_NAME_TAG, $name);
        
        if ($this->dataType === PropertyType::BINARY && $this->binaryValue !== null) {
            $xmlWriter->writeElement(Constants::PROPERTY_VALUE_TAG, base64_encode($this->binaryValue));
        } else {
            $xmlWriter->writeElement(Constants::PROPERTY_VALUE_TAG, $this->stringValue);
        }

        $xmlWriter->writeElement(Constants::PROPERTY_TYPE_TAG, $this->dataType);
        $xmlWriter->endElement();
    }
}