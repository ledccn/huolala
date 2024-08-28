# 货拉拉SDK使用说明



## 安装

`composer require ledc/huolala`



## 使用说明

开箱即用，只需要传入一个配置，初始化一个实例即可：

```php
use Ledc\Huolala\Config;

//更多配置项，可以查看 配置管理类的属性 Ledc\Huolala\Config
$config = [
    'appKey' => '',
    'appSecret' => '',
    'sandbox' => true,
];
$config = new Config($config)
```



## 二次开发

配置管理类：`Ledc\Huolala\Config`

货拉拉基础抽象类：`Ledc\Huolala\BaseAbstract`

你可以继承`Ledc\Huolala\Config`或`Ledc\Huolala\BaseAbstract`，扩展您需要的功能。

在创建实例后，所有的方法都可以有IDE自动补全;



## 捐赠

![reward](reward.png)