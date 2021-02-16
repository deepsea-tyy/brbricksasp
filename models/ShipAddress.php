<?php

namespace bricksasp\models;

use Yii;
use bricksasp\base\Tools;

/**
 * This is the model class for table "{{%ship_address}}".
 *
 * @property int $id
 * @property int|null $owner_id
 * @property int|null $user_id
 * @property int|null $area_id 收货地区ID
 * @property string|null $address 收货详细地址
 * @property string|null $name 收货人姓名
 * @property string|null $phone 收货电话
 * @property int|null $is_default 1是
 * @property string|null $school 学校名称
 * @property string|null $building_no 楼号
 * @property string|null $floor 楼层
 * @property int|null $house_number 门牌号
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class ShipAddress extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%ship_address}}';
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
            [['owner_id', 'user_id', 'area_id', 'is_default', 'house_number', 'created_at', 'updated_at'], 'integer'],
            [['address'], 'string', 'max' => 128],
            [['name'], 'string', 'max' => 8],
            [['building_no','floor'], 'string', 'max' => 8],
            [['phone'], 'string', 'max' => 16],
            [['school', 'school_area'], 'string', 'max' => 32],
            [['phone'], 'checkPhone'],
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
            'area_id' => 'Area ID',
            'address' => 'Address',
            'name' => 'Name',
            'phone' => '手机号',
            'is_default' => 'Is Default',
            'school' => 'School',
            'building_no' => 'Building No',
            'floor' => 'Floor',
            'house_number' => 'House Number',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function checkPhone()
    {
        if (!Tools::is_mobile($this->phone)) {
            $this->addError('phone', '手机号不正确');
        }
    }

    public function saveData($data)
    {
        $this->load($this->formatData($data));
        if (!empty($data['is_default']) && $data['is_default'] == 1) {
            ShipAddress::updateAll(['is_default'=>null],['user_id'=>$data['current_user_id']]);
        }
        return $this->save();
    }
}