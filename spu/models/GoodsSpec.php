<?php

namespace bricksasp\spu\models;

use Yii;

/**
 * This is the model class for table "{{%goods_spec}}".
 *
 * @property int $id
 * @property int|null $owner_id
 * @property int|null $user_id
 * @property string|null $name
 * @property string|null $attr_id
 * @property int|null $param_id
 * @property int|null $is_delete
 */
class GoodsSpec extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_spec}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['owner_id', 'user_id', 'is_delete'], 'integer'],
            [['attr_id', 'param_id'], 'validValue'],
            [['name'], 'string', 'max' => 32],
            [['attr_id', 'param_id'], 'string', 'max' => 255],
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
            'attr_id' => 'Attr ID',
            'param_id' => 'Param ID',
            'is_delete' => 'Is Delete',
        ];
    }

    public function validValue()
    {
        $this->attr_id = json_encode($this->attr_id);
        $this->param_id = json_encode($this->param_id);
    }

    public function saveData($data)
    {
        if (!$this->checkArray($data,['attr_id', 'param_id'])) {
            return false;
        }
        $this->load($this->formatData($data));
        return $this->save();
    }
}
