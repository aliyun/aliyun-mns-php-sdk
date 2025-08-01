<?php

require_once __DIR__ . '/../Common.php';

use AliyunMNS\Client;
use AliyunMNS\Constants;
use AliyunMNS\Exception\MessageNotExistException;
use AliyunMNS\Model\SubscriptionAttributes;
use AliyunMNS\Requests\PublishBase64MessageRequest;
use AliyunMNS\Requests\PublishMessageRequest;
use AliyunMNS\Requests\CreateTopicRequest;
use AliyunMNS\Requests\CreateQueueRequest;
use AliyunMNS\Exception\MnsException;
use AliyunMNS\Model\MessagePropertyValue;
use AliyunMNS\Model\MessageSystemPropertyKey;
use AliyunMNS\Model\MessageSystemPropertyValue;
use AliyunMNS\Model\PropertyType;

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
        // publish raw string message
        $request = new PublishMessageRequest($messageBody);
        try {
        // 设置用户属性
        $request->setUserProperties($this->buildUserProperties());
        $request->setSystemProperties($this->buildSystemProperties());
            $res = $topic->publishMessage($request);
            echo "RawMessagePublished! \n";
        } catch (MnsException $e) {
            echo "PublishRawMessage Failed: " . $e;
            return;
        }

        // publish base64 encoded message
        $request = new PublishBase64MessageRequest($messageBody);
        try {
        // 设置用户属性
        $request->setUserProperties($this->buildUserProperties());
        $request->setSystemProperties($this->buildSystemProperties());
            $res = $topic->publishMessage($request);
            echo "Base64MessagePublished! \n";
        } catch (MnsException $e) {
            echo "PublishBase64Message Failed: " . $e;
            return;
        }

        // 5. start receive message
        // You need to decide whether to perform base64 decoding when receiving messages
        // based on whether the messages pushed to the topic are base64 encoded.
        while (true) {
            try {
                $res = $queue->receiveMessage(3);
                echo "Receive Message success, MessageBody: " . $res->getMessageBody() . "\n";
                $receiptHandle = $res->getReceiptHandle();

                $this->echoUserProperties($res->getUserProperties());
                $this->echoSystemProperties($res->getSystemProperties());

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

    function buildUserProperties()
    {
        $userProperties = [
                    "test-key1" => new MessagePropertyValue(PropertyType::STRING, null, "string property"),
                    "test-key2" => new MessagePropertyValue(PropertyType::BINARY, base64_encode("二进制 property"), null),
                    "test-key3" => new MessagePropertyValue(PropertyType::NUMBER, null, 123),
                    "test-key4" => new MessagePropertyValue(PropertyType::BOOLEAN, null, true),
                ];
        return $userProperties;
    }

    function buildSystemProperties()
    {
        $systemProperties = [
                    MessageSystemPropertyKey::BAGGAGE => new MessageSystemPropertyValue(PropertyType::STRING, "baggage"),
                    MessageSystemPropertyKey::TRACE_PARENT => new MessageSystemPropertyValue(PropertyType::STRING, "traceparent"),
                    MessageSystemPropertyKey::TRACE_STATE => new MessageSystemPropertyValue(PropertyType::STRING, "tracestate"),
                ];
        return $systemProperties;
    }

    function echoUserProperties($userProperties)
    {
        if ($userProperties != NULL) {
                echo "UserProperties: \n";
                foreach ($userProperties as $key => $value)
                    if ($value instanceof MessagePropertyValue) {
                        $dataType = $value->getDataType();
                        if ($dataType === PropertyType::STRING) {
                            echo "Key: " . $key . ", Value: " . $value->getStringValue() . "\n";
                        } elseif ($dataType === PropertyType::BINARY) {
                            // decode the binary data
                            echo "Key: " . $key . ", Value: " . base64_decode($value->getBinaryValue()) . "\n";
                        } elseif ($dataType === PropertyType::NUMBER) {
                            echo "Key: " . $key . ", Value: " . $value->getStringValue() . "\n";
                        } elseif ($dataType === PropertyType::BOOLEAN) {
                            echo "Key: " . $key . ", Value: " . $value->getStringValue() . "\n";
                        } else {
                            echo "Key: ". $key . ", Value: " . $value . "\n";
                        }
                    } else {
                        echo "PropertyType invalid \n";
                    }
            }
    }

    function echoSystemProperties($systemProperties)
    {
        if ($systemProperties != NULL) {
                echo "SystemProperties: \n";
                foreach ($systemProperties as $key => $value)
                    if ($value instanceof MessageSystemPropertyValue) {
                        $dataType = $value->getDataType();
                        if ($dataType === PropertyType::STRING) {
                            echo "Key: " . $key . ", Value: " . $value->getStringValue() . "\n";
                        } else {
                            echo "Key: ". $key . ", Value: " . $value . "\n";
                        }
                    } else {
                        echo "PropertyType invalid \n";
                    }
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


$instance = new CreateTopicAndPushMessageToQueue($accessId, $accessKey, $endPoint);
$instance->run();

?>
