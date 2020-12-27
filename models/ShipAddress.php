<?php

namespace bricksasp\models;

use Yii;

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
 * @property int|null $floor 楼层
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
            [['owner_id', 'user_id', 'area_id', 'is_default', 'floor', 'house_number', 'created_at', 'updated_at'], 'integer'],
            [['address'], 'string', 'max' => 128],
            [['name', 'building_no'], 'string', 'max' => 4],
            [['phone'], 'string', 'max' => 16],
            [['school'], 'string', 'max' => 32],
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
            'phone' => 'Phone',
            'is_default' => 'Is Default',
            'school' => 'School',
            'building_no' => 'Building No',
            'floor' => 'Floor',
            'house_number' => 'House Number',
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