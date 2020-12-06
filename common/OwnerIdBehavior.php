<?php
namespace bricksasp\common;

use Yii;
use yii\behaviors\AttributeBehavior;
use yii\base\InvalidCallException;
use yii\db\BaseActiveRecord;

/**
 * 默认用户字段
 */
class OwnerIdBehavior extends AttributeBehavior
{
    public $onlyAttribute = 'owner_id';

    public $value;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if (empty($this->attributes)) {
            $this->attributes = [
                BaseActiveRecord::EVENT_BEFORE_INSERT => $this->onlyAttribute,
                // BaseActiveRecord::EVENT_BEFORE_UPDATE => $this->onlyAttribute,
            ];
        }
    }

    protected function getValue($event)
    {
        if ($this->value === null) {
            $user = Yii::$app->getUser();
            return $user->identity->owner_id ?? Yii::$app->params['globalParams']['owner_id'];
        }
        return parent::getValue($event);
    }
}
