<?php

namespace bricksasp\models;

use Yii;
use bricksasp\base\Tools;

/**
 * This is the model class for table "basp_runerrands_rider".
 *
 * @property int|null $user_id
 * @property int|null $school_id
 * @property int|null $school_area_id
 * @property string|null $name
 * @property string|null $phone
 * @property int|null $has_car
 * @property int|null $status 1通过2拒绝
 * @property string|null $refuse_reasons
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class RunerrandsRider extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'basp_runerrands_rider';
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
            [['school_id', 'name', 'phone', 'has_car', 'password'], 'required'],
            [['owner_id', 'user_id', 'school_id', 'school_area_id', 'has_car', 'status', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 8],
            [['password'], 'string', 'max' => 32],
            [['phone'], 'string', 'max' => 11],
            [['refuse_reasons'], 'string'],
            [['user_id'], 'unique', 'message' => '请勿重复申请'],
            [['password'], 'checkPassword'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'school_id' => 'School ID',
            'school_area_id' => 'School Area ID',
            'name' => '姓名',
            'phone' => '电话',
            'has_car' => '有无电瓶车',
            'password' => '密码',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function checkPassword()
    {
        $this->password = md5($this->password);
    }

    public function formatData($data)
    {
        $data = parent::formatData($data);
        $sc = StoreRelation::find()->where(['object_id'=>$data['school_id']??null,'type'=>StoreRelation::TYPE_SCHOOL])->one();
        if (!$sc) {
            Tools::breakOff('学校未开放');
        }
        $data['owner_id'] = $sc->owner_id;
        return $data;
    }
}
