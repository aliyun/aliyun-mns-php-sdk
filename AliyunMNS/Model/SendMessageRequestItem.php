<?php
namespace AliyunMNS\Model;

use AliyunMNS\Constants;
use AliyunMNS\Traits\MessagePropertiesForSend;

/**
 * this class is recommended for sendMessage and batchSendMessage.
 */
class SendMessageRequestItem
{
    use MessagePropertiesForSend;

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
        $xmlWriter->endElement();
    }
}

?>
