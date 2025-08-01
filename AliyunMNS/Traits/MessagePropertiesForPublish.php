<?php
namespace AliyunMNS\Traits;

use AliyunMNS\Constants;
use AliyunMNS\Model\MessageAttributes;

trait MessagePropertiesForPublish
{
    use MessageProperties;

    public $messageBody;
    public $messageAttributes;
    public $messageTag;

    public function getMessageBody()
    {
        return $this->messageBody;
    }

    public function setMessageBody($messageBody)
    {
        $this->messageBody = $messageBody;
    }

    public function getMessageAttributes()
    {
        return $this->messageAttributes;
    }

    public function setMessageAttributes($messageAttributes)
    {
        $this->messageAttributes = $messageAttributes;
    }

    public function getMessageTag()
    {
        return $this->messageTag;
    }

    public function setMessageTag($messageTag)
    {
        $this->messageTag = $messageTag;
    }

    public function writeMessagePropertiesForPublishXML(\XMLWriter $xmlWriter)
    {
        if ($this->messageBody != NULL)
        {
            $xmlWriter->writeElement(Constants::MESSAGE_BODY, $this->messageBody);
        }
        if ($this->messageAttributes !== NULL)
        {
            $this->messageAttributes->writeXML($xmlWriter);
        }
        if ($this->messageTag != NULL)
        {
            $xmlWriter->writeElement(Constants::MESSAGE_TAG, $this->messageTag);
        }
        if ($this->userProperties != NULL) {
            $xmlWriter->startElement(Constants::USER_PROPERTIES_TAG);
            foreach ($this->userProperties as $name => $propertyValue) {
                $propertyValue->writeXML($xmlWriter, $name);
            }
            $xmlWriter->endElement();
        }
        if ($this->systemProperties != NULL) {
            $xmlWriter->startElement(Constants::SYSTEM_PROPERTIES_TAG);
            foreach ($this->systemProperties as $name => $propertyValue) {
                $propertyValue->writeXML($xmlWriter, $name);
            }
            $xmlWriter->endElement();
        }
    }
}

?>
