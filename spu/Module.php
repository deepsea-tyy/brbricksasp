<?php
namespace bricksasp\spu;

use Yii;

/**
 * Module module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'bricksasp\spu\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if (!isset(Yii::$app->i18n->translations['spu'])) {
            Yii::$app->i18n->translations['spu'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'zh-cn',
                'basePath' => '@bricksasp/base/messages',
            ];
        }
    }
}
