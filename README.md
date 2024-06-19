# MNS SDK for PHP    

Aliyun MNS Documents: https://www.aliyun.com/product/mns

Aliyun MNS Console: https://mns.console.aliyun.com

## Intall Composer

To install composer by following commands, or see [composer](https://docs.phpcomposer.com/00-intro.html)
```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

## Install & Use

Add require to your `composer.json`
```json
{
  "require": {
     "aliyun/aliyun-mns-php-sdk": ">=1.0.0"
  }
}
```
Use Composer to install requires
```bash
composer install
```

*Note: php version>=5.5.0, and xml extension of php is required.*

## Run the Samples

[Queue Sample](https://github.com/aliyun/aliyun-mns-php-sdk/blob/master/Samples/Queue/CreateQueueAndSendMessage.php)  
[Topic Sample](https://github.com/aliyun/aliyun-mns-php-sdk/blob/master/Samples/Topic/CreateTopicAndPublishMessage.php) 

The basic steps are:

1. Set AliCloud AK/SK In Env, please see: [configure-the-alibaba-cloud-accesskey-environment](https://help.aliyun.com/zh/sdk/developer-reference/configure-the-alibaba-cloud-accesskey-environment-variable-on-linux-macos-and-windows-systems)
2. Run (In the SDK root directory):
   - `CreateQueueAndSendMessage.php` : Set the `Endpoint` at the bottom and Run `php Samples/Queue/CreateQueueAndSendMessage.php`.
   - `CreateTopicAndPushMessageToQueue.php` : Set the `Endpoint` at the bottom and Run `Samples/Topic/CreateTopicAndPushMessageToQueue.php`.
   - `CreateTopicAndPublishMessage.php` : Set the `Endpoint`, `ip` and `port` at the bottom and Run `Samples/Topic/CreateTopicAndPublishMessage.php`.
   - `TopicSubscribe.php` : Set the `Endpoint`, `region` and `accountId` at the bottom and Run `Samples/Topic/TopicSubscribe.php`.

## Run the Tests

The basic steps are:

1. Set AliCloud AK/SK/Endpoint In `Tests/aliyun-mns.ini`.
2. In the SDK root directory, run `vendor/bin/phpunit`.