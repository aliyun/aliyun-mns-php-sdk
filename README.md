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

## Samples

[Queue Sample](https://github.com/aliyun/aliyun-mns-php-sdk/blob/master/Samples/Queue/CreateQueueAndSendMessage.php)
[Topic Sample](https://github.com/aliyun/aliyun-mns-php-sdk/blob/master/Samples/Topic/CreateTopicAndPublishMessage.php) 
