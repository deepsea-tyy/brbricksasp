<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%real_name_auth}}".
 *
 * @property int $owner_id
 * @property int $user_id
 * @property string|null $name
 * @property int|null $gender 性别
 * @property string|null $id_card_no 身份证号
 * @property string|null $id_card_frontal_photo 身份证正面照
 * @property string|null $id_card_reverse_photo 身份证反面照
 * @property int|null $status 0未审核1通过2拒绝
 * @property string|null $refuse_reasons 拒绝原因
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class RealNameAuth extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%real_name_auth}}';
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
            [['user_id'], 'required'],
            [['owner_id', 'user_id', 'gender', 'status', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 4],
            [['id_card_no'], 'string', 'max' => 18],
            [['id_card_frontal_photo', 'id_card_reverse_photo'], 'string', 'max' => 64],
            [['refuse_reasons'], 'string', 'max' => 255],
            [['user_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'name' => 'Name',
            'gender' => 'Gender',
            'id_card_no' => 'Id Card No',
            'id_card_frontal_photo' => 'Id Card Frontal Photo',
            'id_card_reverse_photo' => 'Id Card Reverse Photo',
            'status' => 'Status',
            'refuse_reasons' => 'Refuse Reasons',
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
