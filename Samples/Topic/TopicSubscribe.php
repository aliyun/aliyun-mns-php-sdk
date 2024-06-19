<?php

require_once __DIR__ . '/../Common.php';

use AliyunMNS\Client;
use AliyunMNS\Constants;
use AliyunMNS\Model\SubscriptionAttributes;
use AliyunMNS\Requests\PublishMessageRequest;
use AliyunMNS\Requests\CreateTopicRequest;
use AliyunMNS\Requests\CreateQueueRequest;
use AliyunMNS\Exception\MnsException;
use AliyunMNS\Model\UpdateSubscriptionAttributes;
use AliyunMNS\Requests\BatchReceiveMessageRequest;
use AliyunMNS\Model\Message;
use AliyunMNS\Traits\MessagePropertiesForReceive;
class TopicSubscribe
{
    private $region;
    private $accountId;
    private $accessId;
    private $accessKey;
    private $endPoint;
    private $client;

    public function __construct($region, $accountId, $accessId, $accessKey, $endPoint)
    {
        $this->region = $region;
        $this->accountId = $accountId;
        $this->accessId = $accessId;
        $this->accessKey = $accessKey;
        $this->endPoint = $endPoint;
    }

    public function run()
    {
        $this->client = new Client($this->endPoint, $this->accessId, $this->accessKey);

        // 1. create topic
        $topicName = "TopicSubscribe-demo-topic";
        $request = new CreateTopicRequest($topicName);
        try {
            $res = $this->client->createTopic($request);
            echo "TopicCreated! \n";
        } catch (MnsException $e) {
            echo "CreateTopicFailed: " . $e;
            return;
        }

        // 2. create queue
        $queueName = "TopicSubscribe-demo-queue";
        $request = new CreateQueueRequest($queueName);
        try {
            $res = $this->client->createQueue($request);
            echo "QueueCreated! \n";
        } catch (MnsException $e) {
            echo "CreateQueueFailed: " . $e;
            return;
        }

        // 3. subscribe with filter
        $topic = $this->client->getTopicRef($topicName);
        $subscriptionName = "TopicSubscribe-demo-subscription";
        $queueEndpoint = $this->buildQueueEndpointForSub($this->region, $this->accountId, $queueName);
        echo "queueEndpoint = $queueEndpoint\n";
        $attributes = new SubscriptionAttributes($subscriptionName, $queueEndpoint);
        $attributes->setFilterTag("php-sdk-test-filter");
        try {
            $topic->subscribe($attributes);
            echo "subscribe done \n";
        } catch (MnsException $e) {
            echo "subscribe failed: " . $e;
            return;
        }

        //4. setSubscriptionAttribute
        $topic = $this->client->getTopicRef($topicName);
        $subscriptionName = "TopicSubscribe-demo-subscription";
        $updateAttributes = new UpdateSubscriptionAttributes($subscriptionName,"EXPONENTIAL_DECAY_RETRY");
        try {
            //$topic->setSubscriptionAttribute($updateAttributes);
            echo "setSubscriptionAttribute done\n";
        }catch(MnsException $e){
            echo "setSubscriptionAttribute failed: " . $e;
            return;
        }

        //5. get subscription attributes
        $topic = $this->client->getTopicRef($topicName);
        $subscriptionName = "TopicSubscribe-demo-subscription";
        try {
            $resp = $topic->getSubscriptionAttribute($subscriptionName);
            $attrs = $resp->getSubscriptionAttributes();
            $filterTag = $attrs->getFilterTag();
            echo "filter tag is: $filterTag\n";
        }catch (MnsException $e){
            echo "getSubscriptionAttribute failed: " . $e;
            return;
        }

        //6. send 1st msg with filter tag
        $topic = $this->client->getTopicRef($topicName);
        $messageBody = "TopicSubscribe-demo-message-with-tag";
        $messageTag = "php-sdk-test-filter";
        $request = new PublishMessageRequest($messageBody, $messageTag);
        try
        {
            $topic->publishMessage($request);
            echo "PublishMessageRequest 1st done \n";
        }
        catch (MnsException $e)
        {
            echo "PublishMessageRequest 1st failed: " . $e;
            return;
        }

        //7. send 2nd msg with no filter tag
        $topic = $this->client->getTopicRef($topicName);
        $messageBody = "TopicSubscribe-demo-message-no-tag";
        $request = new PublishMessageRequest($messageBody);
        try
        {
            $topic->publishMessage($request);
            echo "PublishMessageRequest 2nd done \n";
        }
        catch (MnsException $e)
        {
            echo "PublishMessageRequest 2nd failed: " . $e;
            return;
        }

        //8. try to receive messages from queue
        $queueName = "TopicSubscribe-demo-queue";
        //be careful that queue is default set to assume any queue messages are e coded in bse64,
        //and try to decode it,set base64 to FALSE in case of subscription.
        $queue = $this->client->getQueueRef($queueName, FALSE);

        try {
            $request = new BatchReceiveMessageRequest(16, 30);
            echo "start receiving message\n";
            $resp = $queue->batchReceiveMessage($request);
            echo "finish waiting\n";
            $messages = $resp->getMessages();
            foreach($messages as $message){
                $receiptHandle = $message->getReceiptHandle();
                $body = $message->getMessageBody();
                echo "$body\n";
                if(strpos($body, "TopicSubscribe-demo-message-no-tag")!==false){
                    echo "demo failed\n";
                    return;
                }else{
                    try {
                        $queue->deleteMessage($receiptHandle);
                        echo "delete msg: $receiptHandle\n";
                    }catch (MnsException $e){
                        echo "delete msg: $receiptHandle failed\n";
                    }
                }
            }
        }catch (MnsException $e){
            print $e;
        }

        echo "demo finished\n";
    }
    private  function buildQueueEndpointForSub($region,$accountId,$queueName){
        return sprintf("acs:mns:%s:%d:queues/%s",$region,$accountId,$queueName);
    }
}

$region = "";
$accountId ="";
$accessId = getenv(Constants::ALIYUN_AK_ENV_KEY);
$accessKey = getenv(Constants::ALIYUN_SK_ENV_KEY);
$endPoint = "";

if (empty($accessId) || empty($accessKey))
{
    echo "Must Set AccessId/AccessKey In Env to Run the Example. \n";
    return;
}

if (empty($endPoint) || empty($region) || empty($accountId)) {
    echo "Must Provide EndPoint/Region/AccountId to Run the Example. \n";
    return;
}


$instance = new TopicSubscribe($region, $accountId, $accessId, $accessKey, $endPoint);
$instance->run();

?>
