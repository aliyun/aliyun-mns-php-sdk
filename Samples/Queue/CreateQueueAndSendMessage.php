<?php

require_once __DIR__ . '/../Common.php';

use AliyunMNS\Client;
<<<<<<< HEAD
use AliyunMNS\Constants;
=======
>>>>>>> 564fb7f (refactor(sample): refactor samples to add new feature usages)
use AliyunMNS\Model\SendMessageRequestItem;
use AliyunMNS\Requests\BatchReceiveMessageRequest;
use AliyunMNS\Requests\SendMessageRequest;
use AliyunMNS\Requests\CreateQueueRequest;
use AliyunMNS\Exception\MnsException;

class CreateQueueAndSendMessage
{
    private $accessId;
    private $accessKey;
    private $endPoint;
    private $client;

    public function __construct($accessId, $accessKey, $endPoint)
    {
        $this->accessId = $accessId;
        $this->accessKey = $accessKey;
        $this->endPoint = $endPoint;
    }

    public function run()
    {
        $queueName = "CreateQueueAndSendMessageExample";

        $this->client = new Client($this->endPoint, $this->accessId, $this->accessKey);

        // 1. create queue
        $request = new CreateQueueRequest($queueName);
        try
        {
            $res = $this->client->createQueue($request);
            echo "QueueCreated! \n";
        }
        catch (MnsException $e)
        {
            echo "CreateQueueFailed: " . $e;
            return;
        }
        $queue = $this->client->getQueueRef($queueName);

        // Base64 is enabled by default and can be disabled using queue->setBase64(false);

        // 2. send message
        $messageBody = "test";
        $bodyMD5 = md5(base64_encode($messageBody));
        // as the messageBody will be automatically encoded
        // the MD5 is calculated for the encoded body
        // 2.1 use SendMessageRequest to send message.(Not Recommend)
        // the base64 you set to SendMessageRequest is invalid
        // whether to execute base64 encode depends on the queue base64
        $request = new SendMessageRequest($messageBody);
        try
        {
            $res = $queue->sendMessage($request);
            echo "MessageSent! \n";
        }
        catch (MnsException $e)
        {
            echo "SendMessage Failed: " . $e;
            return;
        }

        // 2.2 use SendMessageRequestItem to send message.(Recommend)
        $requestItem = new SendMessageRequestItem($messageBody);
        try
        {
            $res = $queue->sendMessage($requestItem);
            echo "MessageSent! \n";
        }
        catch (MnsException $e)
        {
            echo "SendMessage Failed: " . $e;
            return;
        }

        // 3. peek message
        try
        {
            $res = $queue->peekMessage();
            echo "PeekMessage Succeed! \n";
            if (strtoupper($bodyMD5) == $res->getMessageBodyMD5())
            {
                echo "You got the message sent by yourself! \n";
            }
        }
        catch (MnsException $e)
        {
            echo "PeekMessage Failed: " . $e;
            return;
        }

        // 4. receive message
        $receiptHandle = NULL;
        try
        {
            // when receiving messages, it's always a good practice to set the waitSeconds to be 30.
            // it means to send one http-long-polling request which lasts 30 seconds at most.
            $res = $queue->receiveMessage(30);
            echo "ReceiveMessage Succeed! \n";
            if (strtoupper($bodyMD5) == $res->getMessageBodyMD5())
            {
                echo "You got the message sent by yourself! \n";
            }
            $receiptHandle = $res->getReceiptHandle();
        }
        catch (MnsException $e)
        {
            echo "ReceiveMessage Failed: " . $e;
            return;
        }

        // 5. delete message
        try
        {
            $res = $queue->deleteMessage($receiptHandle);
            echo "DeleteMessage Succeed! \n";
        }
        catch (MnsException $e)
        {
            echo "DeleteMessage Failed: " . $e;
            return;
        }

        // 关闭 base64
        $queue->setBase64(false);

        // 6. batch send message
        try {
            // 创建 SendMessageRequestItem 数组
            $requestItems = array();
            for ($i = 0; $i < 16; $i++) {
                $messageBody = "test" . $i;
                $requestItems[] = new SendMessageRequestItem($messageBody);
            }
            $res = $queue->batchSendMessage($requestItems);
            echo "BatchSendMessage Succeed! \n";
        }
        catch (MnsException $e)
        {
            echo "BatchSendMessage Failed: " . $e;
            return;
        }

        // 7. batch peek message
        try {
            $res = $queue->batchPeekMessage(3);
            echo "BatchPeekMessage Succeed! \n";
        }
        catch (MnsException $e)
        {
            echo "BatchPeekMessage Failed: " . $e;
            return;
        }

        // 8. batch receive message
        try {
            $request = new BatchReceiveMessageRequest(3, 30);
            $res = $queue->batchReceiveMessage($request);
            $receiptHandles = array();
            for ($i = 0; $i < count($res->getMessages()); $i++) {
                $receiptHandles[] = $res->getMessages()[$i]->getReceiptHandle();
            }
            echo "BatchReceiveMessage Succeed! \n";
        }
        catch (MnsException $e)
        {
            echo "BatchReceiveMessage Failed: " . $e;
            return;
        }

        // 9. batch delete message
        try {
            $res = $queue->batchDeleteMessage($receiptHandles);
            echo "BatchDeleteMessage Succeed! \n";
        }
        catch (MnsException $e)
        {
            echo "BatchDeleteMessage Failed: " . $e;
            return;
        }


        // 10. delete queue
        try {
            $this->client->deleteQueue($queueName);
            echo "DeleteQueue Succeed! \n";
        } catch (MnsException $e) {
            echo "DeleteQueue Failed: " . $e;
            return;
        }
    }
}

$accessId = getenv(Constants::ALIYUN_AK_ENV_KEY);
$accessKey = getenv(Constants::ALIYUN_SK_ENV_KEY);
$endPoint = "";

if (empty($accessId) || empty($accessKey))
{
    echo "Must Set AccessId/AccessKey In Env to Run the Example. \n";
    return;
}

if (empty($endPoint)) {
    echo "Must Provide EndPoint to Run the Example. \n";
    return;
}

$instance = new CreateQueueAndSendMessage($accessId, $accessKey, $endPoint);
$instance->run();

?>
