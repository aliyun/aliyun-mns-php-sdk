<?php
namespace AliyunMNS\Model;

use AliyunMNS\Constants;
use AliyunMNS\Traits\MessagePropertiesForSend;
use AliyunMNS\Traits\MessageUserProperties;

/**
 * this class is recommended for sendMessage and batchSendMessage.
 */
class SendMessageRequestItem
{
    use MessagePropertiesForSend;
    use MessageUserProperties;

    public function __construct($messageBody, $delaySeconds = NULL, $priority = NULL)
    {
        $this->messageBody = $messageBody;
        $this->delaySeconds = $delaySeconds;
        $this->priority = $priority;
    }

    public function writeXML(\XMLWriter $xmlWriter, $base64)
    {
        $xmlWriter->startELement('Message');
        $this->writeMessagePropertiesForSendXML($xmlWriter, $base64);
        $this->writeXMLForUserProperties($xmlWriter);
        $xmlWriter->endElement();
    }
}

?>
