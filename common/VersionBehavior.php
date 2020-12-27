<?php
namespace bricksasp\common;

use yii\db\BaseActiveRecord;
use yii\base\InvalidCallException;
use yii\behaviors\AttributeBehavior;

/**
 * 默认版本号字段
 */
class VersionBehavior extends AttributeBehavior
{
    public $onlyAttribute = 'version';

    public $value;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if (empty($this->onlyAttribute)) {
            $this->onlyAttribute = [
                BaseActiveRecord::EVENT_BEFORE_INSERT => $this->onlyAttribute,
                BaseActiveRecord::EVENT_BEFORE_UPDATE => $this->onlyAttribute,
            ];
        }
    }

    protected function getValue($event)
    {
        if ($this->value === null) {
            $field = $this->onlyAttribute;
            return (int)$this->owner->$field + 1;
        }

        return parent::getValue($event);
    }

    public function touch($attribute)
    {
        /* @var $owner BaseActiveRecord */
        $owner = $this->owner;
        if ($owner->getIsNewRecord()) {
            throw new InvalidCallException('Updating the version is not possible on a new record.');
        }
        $owner->updateAttributes(array_fill_keys((array) $attribute, $this->getValue(null)));
    }
}
