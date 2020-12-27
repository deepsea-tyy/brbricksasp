<?php
namespace bricksasp\common;

use Yii;
use yii\db\BaseActiveRecord;
use yii\base\InvalidCallException;
use yii\behaviors\AttributeBehavior;

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
        if (empty($this->onlyAttribute)) {
            $this->onlyAttribute = [
                BaseActiveRecord::EVENT_BEFORE_INSERT => $this->onlyAttribute,
            ];
        }
    }

    protected function getValue($event)
    {
        if ($this->value === null) {
            $temp = Yii::$app->redis->get($this->key);
            if(!$temp){
                $field = $this->onlyAttribute;
                Yii::$app->redis->set($this->key, $this->number);
            }
            return Yii::$app->redis->incr($this->key);
        }
        return parent::getValue($event);
    }
}
