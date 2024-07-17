<?php

use AliyunMNS\Client;

abstract class PHPUnit8Base extends \PHPUnit_Framework_TestCase
{
    public $client;
    public $queueToDelete;
    public $topicToDelete;

    protected function setUp(): void
    {
        $ini_array = parse_ini_file(__DIR__ . "/aliyun-mns.ini");

        $endPoint = $ini_array["endpoint"];
        $accessId = $ini_array["accessid"];
        $accessKey = $ini_array["accesskey"];

        $this->queueToDelete = array();
        $this->topicToDelete = array();

        $this->client = new Client($endPoint, $accessId, $accessKey);
    }

    protected function tearDown(): void
    {
        foreach ($this->queueToDelete as $queueName) {
            try {
                $this->client->deleteQueue($queueName);
            } catch (\Exception $e) {
            }
        }
        foreach ($this->topicToDelete as $topicName) {
            try {
                $this->client->deleteTopic($topicName);
            } catch (\Exception $e) {
            }
        }
    }
}

?>
