<?php

use AliyunMNS\Client;
use AliyunMNS\Exception\MessageNotExistException;
use AliyunMNS\Model\SubscriptionAttributes;
use AliyunMNS\Requests\PublishMessageRequest;
use AliyunMNS\Requests\CreateTopicRequest;
use AliyunMNS\Requests\CreateQueueRequest;
use AliyunMNS\Exception\MnsException;

class CreateTopicAndPushMessageToQueue
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
        $topicName = "CreateTopicAndPushMessageToQueueExample";
        $queueName = "CreateTopicAndPushMessageToQueueExample";

        $this->client = new Client($this->endPoint, $this->accessId, $this->accessKey);

        // 1. create topic
        $request = new CreateTopicRequest($topicName);
        try {
            $res = $this->client->createTopic($request);
            echo "TopicCreated! \n";
        } catch (MnsException $e) {
            echo "CreateTopicFailed: " . $e;
            return;
        }
        $topic = $this->client->getTopicRef($topicName);

        // 2. create queue
        $request = new CreateQueueRequest($queueName);
        try {
            $res = $this->client->createQueue($request);
            echo "QueueCreated! \n";
        } catch (MnsException $e) {
            echo "CreateQueueFailed: " . $e;
            return;
        }
        $queue = $this->client->getQueueRef($queueName);

        // 3. subscribe
        $subscriptionName = "SubscriptionExample";
        $attributes = new SubscriptionAttributes($subscriptionName, $topic->generateQueueEndpoint($queueName), 'BACKOFF_RETRY', 'SIMPLIFIED');
        try {
            $topic->subscribe($attributes);
            echo "Subscribed! \n";
        } catch (MnsException $e) {
            echo "SubscribeFailed: " . $e;
            return;
        }

        // 4. send message
        $messageBody = "test";
        $request = new PublishMessageRequest(base64_encode($messageBody));
        try {
            $res = $topic->publishMessage($request);
            echo "MessagePublished! \n";
        } catch (MnsException $e) {
            echo "PublishMessage Failed: " . $e;
            return;
        }

        // 5. start receive message
        while (true) {
            try {
                $res = $queue->receiveMessage(3);
                echo "Receive Message success, MessageBody: " . $res->getMessageBody() . "\n";
                $receiptHandle = $res->getReceiptHandle();
                $queue->deleteMessage($receiptHandle);
                echo "DeleteMessage Succeed! \n";
                break;
            } catch (MessageNotExistException $e) {
                echo "No New Message";
            } catch (MnsException $e) {
                echo "Process Failed: " . $e;
            }
        }

        // 6. unsubscribe
        try {
            $topic->unsubscribe($subscriptionName);
            echo "Unsubscribe Succeed! \n";
        } catch (MnsException $e) {
            echo "Unsubscribe Failed: " . $e;
            return;
        }

        // 7. delete topic
        try {
            $this->client->deleteTopic($topicName);
            echo "DeleteTopic Succeed! \n";
        } catch (MnsException $e) {
            echo "DeleteTopic Failed: " . $e;
            return;
        }

        // 8. delete queue
        try {
            $this->client->deleteQueue($queueName);
            echo "DeleteQueue Succeed! \n";
        } catch (MnsException $e) {
            echo "DeleteQueue Failed: " . $e;
            return;
        }
    }
}

$accessId = "";
$accessKey = "";
$endPoint = "";

if (empty($accessId) || empty($accessKey) || empty($endPoint)) {
    echo "Must Provide AccessId/AccessKey/EndPoint to Run the Example. \n";
    return;
}


$instance = new CreateTopicAndPushMessageToQueue($accessId, $accessKey, $endPoint);
$instance->run();

?>
