<?php

namespace bricksasp\spu\models;

use Yii;

/**
 * This is the model class for table "{{%goods_attr}}".
 *
 * @property int $id
 * @property int|null $owner_id
 * @property int|null $user_id
 * @property string|null $name
 * @property int|null $sort
 * @property int|null $is_delete
 * @property string|null $value
 * @property int|null $type 1属性2参数
 * @property string|null $text_type checkbox|radio|text
 */
class GoodsAttr extends \bricksasp\base\BaseActiveRecord
{
    const TYPE_ATTR = 1;
    const TYPE_PARAM = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_attr}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['owner_id', 'user_id', 'sort', 'is_delete', 'type'], 'integer'],
            [['value'], 'validValue'],
            [['name'], 'string', 'max' => 32],
            [['text_type'], 'string', 'max' => 16],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'owner_id' => 'Owner ID',
            'user_id' => 'User ID',
            'name' => 'Name',
            'sort' => 'Sort',
            'is_delete' => 'Is Delete',
            'value' => 'Value',
            'type' => 'Type',
            'text_type' => 'Text Type',
        ];
    }
    
    public function validValue()
    {
        $this->value = json_encode($this->value,JSON_UNESCAPED_UNICODE);
    }

    public function saveData($data)
    {
        if (!$this->checkArray($data,['value'])) {
            return false;
        }
        $this->load($this->formatData($data));
        return $this->save();
    }
}
