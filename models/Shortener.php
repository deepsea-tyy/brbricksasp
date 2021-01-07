<?php

namespace bricksasp\models;

use Yii;
use bricksasp\base\Tools;

/**
 * This is the model class for table "{{%shortener}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $scene
 * @property string|null $code 显示值
 * @property int|null $type 1商品2文章3酒卡4个人
 * @property string|null $object_id
 * @property int|null $is_delete 1使用后删除
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class Shortener extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%shortener}}';
    }

    public function behaviors()
    {
        return [
            \yii\behaviors\TimestampBehavior::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['scene', 'type'], 'required'],
            [['user_id', 'scene', 'type', 'is_delete', 'created_at', 'updated_at'], 'integer'],
            [['type'], 'checkType'],
            [['code', 'object_id'], 'string', 'max' => 32],
            [['code'], 'unique'],
        ];
    }

    public function checkType()
    {
        if ($this->type == 4) {
            $uinfo = UserInfo::find()->select(['invitation'])->where(['user_id'=>$this->user_id])->one();
            $this->code = $uinfo->invitation;
            $this->is_delete = 0;
        }else{
            if (!$this->object_id) {
                $this->addError('object_id','object_id不能为空');
            }
            $this->is_delete = 1;
            $this->code = Tools::random_str(6);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'type' => 'Type',
            'object_id' => 'Object ID',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
    
    public function saveData($data)
    {
        $this->load($this->formatData($data));
        return $this->save();
    }
}
