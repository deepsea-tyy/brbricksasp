<?php

namespace bricksasp\models;

use Yii;
use bricksasp\base\Tools;
use bricksasp\models\redis\Token;

/**
 * This is the model class for table "{{%runerrands_rider}}".
 *
 * @property int $user_id
 * @property int|null $owner_id
 * @property int|null $school_id
 * @property int|null $school_area_id
 * @property string|null $name
 * @property string|null $phone
 * @property int|null $has_car
 * @property int|null $status
 * @property string|null $refuse_reasons
 * @property string|null $password
 * @property int|null $tmp_msg 1订阅消息通知
 * @property int|null $work_status 1接单中
 * @property int|null $day_order 日单数
 * @property int|null $total_order 累计单数
 * @property float|null $day_money
 * @property float|null $total_amount
 * @property int|null $passa_at 审核通过时间
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
            [['user_id', 'owner_id', 'school_id', 'school_area_id', 'has_car', 'status', 'tmp_msg', 'work_status', 'day_order', 'total_order', 'passa_at','created_at', 'updated_at'], 'integer'],
            [['refuse_reasons'], 'string'],
            [['day_money', 'total_amount'], 'number'],
            [['name'], 'string', 'max' => 8],
            [['phone'], 'string', 'max' => 11],
            [['password'], 'string', 'max' => 64],
            [['user_id'], 'unique', 'message' => '请勿重复申请'],
            [['password'], 'checkPassword'],
            [['day_money', 'total_amount', 'day_order', 'total_order'], 'default', 'value'=>0],
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

    public function getUserInfo()
    {
        return $this->hasOne(UserInfo::className(),['user_id'=>'user_id'])->select(['user_id','avatar'])->with(['file']);
    }

    public function getSchool()
    {
        return $this->hasOne(School::className(), ['id'=>'school_id'])->select(['id', 'name']);
    }

    public function getSchoolArea()
    {
        return $this->hasOne(School::className(), ['id'=>'school_area_id'])->select(['id', 'name']);
    }

    public function getRealName()
    {
        return $this->hasOne(RealNameAuth::className(),['user_id'=>'user_id']);
    }

    public function formatData($data)
    {
        $data = parent::formatData($data);
        if ($this->isNewRecord && Token::TOKEN_TYPE_FRONTEND == $data['current_login_type']) {
            $sc = SchoolRelation::find()->where(['object_id'=>$data['school_id']??null,'type'=>SchoolRelation::TYPE_SCHOOL])->one();
            if (!$sc) {
                Tools::breakOff('学校未开放');
            }
            $data['owner_id'] = $sc->owner_id;
        }
        if (!empty($data['status']) && $data['status'] == 1) {
            $data['passa_at'] = time();
        }
        return $data;
    }
}
