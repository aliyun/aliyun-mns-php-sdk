<?php

require_once __DIR__ . '/Compatibility.php';

use AliyunMNS\Topic;
use AliyunMNS\Constants;
use AliyunMNS\Model\TopicAttributes;
use AliyunMNS\Model\MailAttributes;
use AliyunMNS\Model\SmsAttributes;
use AliyunMNS\Model\BatchSmsAttributes;
use AliyunMNS\Model\MessageAttributes;
use AliyunMNS\Model\SubscriptionAttributes;
use AliyunMNS\Model\UpdateSubscriptionAttributes;
use AliyunMNS\Exception\MnsException;
use AliyunMNS\Requests\CreateQueueRequest;
use AliyunMNS\Requests\CreateTopicRequest;
use AliyunMNS\Requests\PublishMessageRequest;
use AliyunMNS\Model\MessagePropertyValue;
use AliyunMNS\Model\MessageSystemPropertyKey;
use AliyunMNS\Model\MessageSystemPropertyValue;
use AliyunMNS\Model\PropertyType;

class TopicTest extends PHPUnitBase
{
    private function prepareTopic($topicName, $attributes = NULL)
    {
        $request = new CreateTopicRequest($topicName, $attributes);
        $this->topicToDelete[] = $topicName;
        try
        {
            $res = $this->client->createTopic($request);
            $this->assertTrue($res->isSucceed());
        }
        catch (MnsException $e)
        {
            $this->assertTrue(FALSE, $e);
        }

        return $this->client->getTopicRef($topicName);
    }

    private function prepareSubscription(Topic $topic, $subscriptionName)
    {
        try
        {
            $attributes = new SubscriptionAttributes($subscriptionName, 'https://www.baidu.com/', 'BACKOFF_RETRY', 'XML');
            $topic->subscribe($attributes);
        }
        catch (MnsException $e)
        {
        }
    }

    public function testLoggingEnabled()
    {
        $topicName = "testLoggingEnabled";
        $topic = $this->prepareTopic($topicName);

        try
        {
            $attributes = new TopicAttributes;
            $attributes->setLoggingEnabled(false);
            $topic->setAttribute($attributes);
            $res = $topic->getAttribute();
            $this->assertTrue($res->isSucceed());
            $this->assertEquals(false, $res->getTopicAttributes()->getLoggingEnabled());

            $attributes = new TopicAttributes;
            $attributes->setLoggingEnabled(true);
            $topic->setAttribute($attributes);
            $res = $topic->getAttribute();
            $this->assertTrue($res->isSucceed());
            $this->assertEquals(true, $res->getTopicAttributes()->getLoggingEnabled());

            $attributes = new TopicAttributes;
            $topic->setAttribute($attributes);
            $res = $topic->getAttribute();
            $this->assertTrue($res->isSucceed());
            $this->assertEquals(true, $res->getTopicAttributes()->getLoggingEnabled());
        }
        catch (MnsException $e)
        {
            $this->assertTrue(FALSE, $e);
        }
    }

    public function testTopicAttributes()
    {
        $topicName = "testTopicAttributes";
        $topic = $this->prepareTopic($topicName);

        try
        {
            $res = $topic->getAttribute();
            $this->assertTrue($res->isSucceed());
            $this->assertEquals($topicName, $res->getTopicAttributes()->getTopicName());
        }
        catch (MnsException $e)
        {
            $this->assertTrue(FALSE, $e);
        }

        $maximumMessageSize = 10 * 1024;
        $attributes = new TopicAttributes;
        $attributes->setMaximumMessageSize($maximumMessageSize);
        try
        {
            $res = $topic->setAttribute($attributes);
            $this->assertTrue($res->isSucceed());
        }
        catch (MnsException $e)
        {
            $this->assertTrue(FALSE, $e);
        }

        try
        {
            $res = $topic->getAttribute();
            $this->assertTrue($res->isSucceed());
            $this->assertEquals($res->getTopicAttributes()->getMaximumMessageSize(), $maximumMessageSize);
        }
        catch (MnsException $e)
        {
            $this->assertTrue(FALSE, $e);
        }

        $this->client->deleteTopic($topicName);

        try
        {
            $res = $topic->getAttribute();
            $this->assertTrue(False, "Should throw TopicNotExistException");
        }
        catch (MnsException $e)
        {
            $this->assertEquals($e->getMnsErrorCode(), Constants::TOPIC_NOT_EXIST);
        }

        try
        {
            $res = $topic->setAttribute($attributes);
            $this->assertTrue(False, "Should throw TopicNotExistException");
        }
        catch (MnsException $e)
        {
            $this->assertEquals($e->getMnsErrorCode(), Constants::TOPIC_NOT_EXIST);
        }
    }

    public function testPublishMessage()
    {
        $topicName = "testPublishMessage" . uniqid();

        $messageBody = "test";
        $request = new PublishMessageRequest($messageBody);

        $topic = $this->prepareTopic($topicName);
        try
        {
            $res = $topic->publishMessage($request);
            $this->assertTrue($res->isSucceed());
        }
        catch (MnsException $e)
        {
            $this->assertTrue(FALSE, $e);
        }

        $this->client->deleteTopic($topic->getTopicName());
        try
        {
            $res = $topic->publishMessage($request);
            $this->assertTrue(False, "Should throw TopicNotExistException");
        }
        catch (MnsException $e)
        {
            $this->assertEquals($e->getMnsErrorCode(), Constants::TOPIC_NOT_EXIST);
        }
    }

    public function testPublishBatchSmsMessage()
    {
        $topicName = "testPublishBatchSmsMessage" . uniqid();

        // now sub and send message
        $messageBody = "test";

        $topic = $this->prepareTopic($topicName);
        try
        {
            $smsEndpoint = $topic->generateSmsEndpoint();

            $subscriptionName = 'testSubscribeSubscription' . uniqid();
            $attributes = new SubscriptionAttributes($subscriptionName, $smsEndpoint);
            $topic->subscribe($attributes);

            $batchSmsAttributes = new BatchSmsAttributes("陈舟锋", "SMS_15535414");
            $batchSmsAttributes->addReceiver("13735576932", array("name" => "phpsdk-batchsms"));
            $messageAttributes = new MessageAttributes(array($batchSmsAttributes));
            $request = new PublishMessageRequest($messageBody, NULL, $messageAttributes);

            $res = $topic->publishMessage($request);
            $this->assertTrue($res->isSucceed());
//            echo $res->getMessageId();
            sleep(5);
        }
        catch (MnsException $e)
        {
            $this->assertTrue(FALSE, $e);
        }

        $this->client->deleteTopic($topic->getTopicName());
    }

    public function testPublishDirectSmsMessage()
    {
        $topicName = "testPublishDirectSmsMessage" . uniqid();

        // now sub and send message
        $messageBody = "test";

        $topic = $this->prepareTopic($topicName);
        try
        {
            $smsEndpoint = $topic->generateSmsEndpoint();

            $subscriptionName = 'testSubscribeSubscription' . uniqid();
            $attributes = new SubscriptionAttributes($subscriptionName, $smsEndpoint);
            $topic->subscribe($attributes);

            $smsParams = array("name" => "phpsdk");
            $smsAttributes = new SmsAttributes("陈舟锋", "SMS_15535414", $smsParams, "13735576932");
            $messageAttributes = new MessageAttributes($smsAttributes);
            $request = new PublishMessageRequest($messageBody, NULL, $messageAttributes);

            $res = $topic->publishMessage($request);
            $this->assertTrue($res->isSucceed());
//            echo $res->getMessageId();
            sleep(5);
        }
        catch (MnsException $e)
        {
            $this->assertTrue(FALSE, $e);
        }

        $this->client->deleteTopic($topic->getTopicName());
    }

    public function testPublishMailMessage()
    {
        $topicName = "testPublishMailMessage" . uniqid();

        // now sub and send message
        $messageBody = "test";

        $topic = $this->prepareTopic($topicName);
        try
        {
            $mailEndpoint = $topic->generateMailEndpoint("liji.canglj@alibaba-inc.com");

            $subscriptionName = 'testSubscribeSubscription' . uniqid();
            $attributes = new SubscriptionAttributes($subscriptionName, $mailEndpoint);
            $topic->subscribe($attributes);

            $mailAttributes = new MailAttributes("TestSubject", "TestAccountName");
            $messageAttributes = new MessageAttributes($mailAttributes);
            $request = new PublishMessageRequest($messageBody, NULL, $messageAttributes);

            $res = $topic->publishMessage($request);
            $this->assertTrue($res->isSucceed());
//            echo $res->getMessageId();
            sleep(5);
        }
        catch (MnsException $e)
        {
            $this->assertTrue(FALSE, $e);
        }

        $this->client->deleteTopic($topic->getTopicName());
    }

    public function testPublishQueueMessage()
    {
        $topicName = "testPublishQueueMessage" . uniqid();

        // prepare the queue
        $queueName = "testPublishQueueMessageQueue";
        $this->client->deleteQueue($queueName);
        $request = new CreateQueueRequest($queueName);
        $this->client->createQueue($request);

        // now sub and send message
        $messageBody = "test";
        $messageId = "";

        $topic = $this->prepareTopic($topicName);
        try
        {
            $queue = $this->client->getQueueRef($queueName, FALSE);

            $queueEndpoint = $topic->generateQueueEndpoint($queueName);
            //echo($queueEndpoint);

            $subscriptionName = 'testSubscribeSubscription' . uniqid();
            $attributes = new SubscriptionAttributes($subscriptionName, $queueEndpoint);
            $topic->subscribe($attributes);

            $request = new PublishMessageRequest($messageBody);

            $request->setUserProperties($this->buildUserProperties());
            $request->setSystemProperties($this->buildSystemProperties());

            $res = $topic->publishMessage($request);
            $this->assertTrue($res->isSucceed());
            $messageId = $res->getMessageId();

            $res = $queue->receiveMessage(30);
            // topic 模型 前后 md5和 messageId 就是不一致的,这里只检查 消息内容
            $this->assertTrue(strpos($res->getMessageBody(), "<Message>" . $messageBody . "</Message>") >= 0);

            $userProperties = $res->getUserProperties();
            $this->assertEquals(4, count($userProperties));
            $this->assertEquals("string property", $userProperties["test-key1"]->getStringValue());
            $systemProperties = $res->getSystemProperties();
            $this->assertEquals(3, count($systemProperties));
            $this->assertEquals("baggage", $systemProperties[MessageSystemPropertyKey::BAGGAGE]->getStringValue());
            
        }
        catch (MnsException $e)
        {
            $this->assertTrue(FALSE, $e);
        }

        $this->client->deleteTopic($topic->getTopicName());
        $this->client->deleteQueue($queueName);
    }

    public function testSubscribe()
    {
        $topicName = 'testSubscribeTopic' . uniqid();
        $topic = $this->prepareTopic($topicName);

        $subscriptionName = 'testSubscribeSubscription' . uniqid();
        $attributes = new SubscriptionAttributes($subscriptionName, 'https://www.baidu.com/', 'BACKOFF_RETRY', 'XML');
        try
        {
            $topic->subscribe($attributes);
        }
        catch (MnsException $e)
        {
            $this->assertTrue(FALSE, $e);
        }

        try
        {
            $attributes->setContentFormat('SIMPLIFIED');
            $res = $topic->subscribe($attributes);
            $this->assertTrue(False, "Should throw SubscriptionAlreadyExist");
        }
        catch (MnsException $e)
        {
            $this->assertEquals($e->getMnsErrorCode(), Constants::SUBSCRIPTION_ALREADY_EXIST);
        }

        $topic->unsubscribe($subscriptionName);
    }

    public function testSubscriptionAttributes()
    {
        $topicName = "testSubscriptionAttributes" . uniqid();
        $subscriptionName = "testSubscriptionAttributes" . uniqid();
        $topic = $this->prepareTopic($topicName);
        $this->prepareSubscription($topic, $subscriptionName);

        try
        {
            $res = $topic->getSubscriptionAttribute($subscriptionName);
            $this->assertTrue($res->isSucceed());
            $this->assertEquals($topicName, $res->getSubscriptionAttributes()->getTopicName());
            $this->assertEquals('BACKOFF_RETRY', $res->getSubscriptionAttributes()->getStrategy());
        }
        catch (MnsException $e)
        {
            $this->assertTrue(FALSE, $e);
        }

        $strategy = 'EXPONENTIAL_DECAY_RETRY';
        $attributes = new UpdateSubscriptionAttributes($subscriptionName);
        $attributes->setStrategy($strategy);
        try
        {
            $res = $topic->setSubscriptionAttribute($attributes);
            $this->assertTrue($res->isSucceed());
        }
        catch (MnsException $e)
        {
            $this->assertTrue(FALSE, $e);
        }

        try
        {
            $res = $topic->getSubscriptionAttribute($subscriptionName);
            $this->assertTrue($res->isSucceed());
            $this->assertEquals($res->getSubscriptionAttributes()->getStrategy(), $strategy);
        }
        catch (MnsException $e)
        {
            $this->assertTrue(FALSE, $e);
        }

        $topic->unsubscribe($subscriptionName);

        try
        {
            $res = $topic->getSubscriptionAttribute($subscriptionName);
            $this->assertTrue(False, "Should throw SubscriptionNotExistException");
        }
        catch (MnsException $e)
        {
            $this->assertEquals($e->getMnsErrorCode(), Constants::SUBSCRIPTION_NOT_EXIST);
        }

        try
        {
            $res = $topic->setSubscriptionAttribute($attributes);
            $this->assertTrue(False, "Should throw SubscriptionNotExistException");
        }
        catch (MnsException $e)
        {
            $this->assertEquals($e->getMnsErrorCode(), Constants::SUBSCRIPTION_NOT_EXIST);
        }
    }

    public function testListSubscriptions()
    {
        $topicName = "testListSubscriptionsTopic" . uniqid();
        $subscriptionNamePrefix = uniqid();
        $subscriptionName1 = $subscriptionNamePrefix . "testListTopic1";
        $subscriptionName2 = $subscriptionNamePrefix . "testListTopic2";

        // 1. create Topic and Subscriptions
        $topic = $this->prepareTopic($topicName);
        $this->prepareSubscription($topic, $subscriptionName1);
        $this->prepareSubscription($topic, $subscriptionName2);

        // 2. list subscriptions
        $subscriptionName1Found = FALSE;
        $subscriptionName2Found = FALSE;

        $count = 0;
        $marker = '';
        while ($count < 2) {
            try
            {
                $res = $topic->listSubscription(1, $subscriptionNamePrefix, $marker);
                $this->assertTrue($res->isSucceed());

                $subscriptionNames = $res->getSubscriptionNames();
                foreach ($subscriptionNames as $subscriptionName)
                {
                    if ($subscriptionName == $subscriptionName1)
                    {
                        $subscriptionName1Found = TRUE;
                    }
                    elseif ($subscriptionName == $subscriptionName2)
                    {
                        $subscriptionName2Found = TRUE;
                    }
                    else
                    {
                        $this->assertTrue(FALSE, $subscriptionName . " Should not be here.");
                    }
                }

                if ($count > 0)
                {
                    $this->assertTrue($res->isFinished(), implode(", ", $subscriptionNames));
                }
                $marker = $res->getNextMarker();
            }
            catch (MnsException $e)
            {
                $this->assertTrue(FALSE, $e);
            }
            $count += 1;
        }

        $this->assertTrue($subscriptionName1Found, $subscriptionName1 . " Not Found!");
        $this->assertTrue($subscriptionName2Found, $subscriptionName2 . " Not Found!");
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

?>
