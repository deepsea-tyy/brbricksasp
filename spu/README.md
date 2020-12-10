# bricksasp-spu

## 简介
bricksasp 商品接口

安装
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist bricksasp/spu: "~1.0"
```

or add

```json
"bricksasp/spu": "~1.0"
```

to the require section of your composer.json.


Configuration
-------------

To use this extension, you have to configure the Connection class in your application configuration:

```php
return [
    //....
    'components' => [
        'spu' => [
            'class' => 'bricksasp\spu\Module',
        ],
    ]
];
```