<?php
namespace bricksasp\common;

use app\models\ChannelQualification;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\base\InvalidCallException;
use yii\db\BaseActiveRecord;

/**
 * 默认用户字段
 */
class ShowIdBehavior extends AttributeBehavior
{
    public $onlyAttribute = 'show_id';

    public $value;

    public $number;

    public $key;




    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if (empty($this->attributes)) {
            $this->attributes = [
                BaseActiveRecord::EVENT_BEFORE_INSERT => $this->onlyAttribute,
            ];
        }
    }

    protected function getValue($event)
    {
        if ($this->value === null) {
            $temp = Yii::$app->redis->get($this->key);
            if(!$temp){
                $one = ChannelQualification::find()->select([$this->onlyAttribute])->orderBy($this->onlyAttribute.' desc')->one();
                $field = $this->onlyAttribute;
                Yii::$app->redis->set($this->key, $one ? $one->$field : $this->number);
            }
            return Yii::$app->redis->incr($this->key);
        }
        return parent::getValue($event);
    }
}
