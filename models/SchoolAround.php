<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "basp_school_around".
 *
 * @property int $id
 * @property int|null $owner_id
 * @property int|null $user_id
 * @property int|null $school_id 学校id
 * @property string|null $name 地点名称
 * @property string|null $logo
 * @property string|null $address 详细地址
 * @property int|null $area_id
 * @property int|null $type 1取快递2外卖代拿3跑腿
 * @property string|null $lat
 * @property string|null $lon
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class SchoolAround extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'basp_school_around';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['owner_id', 'user_id', 'school_id', 'area_id', 'type', 'created_at', 'updated_at'], 'integer'],
            [['name', 'logo'], 'string', 'max' => 64],
            [['address'], 'string', 'max' => 128],
            [['lat', 'lon'], 'string', 'max' => 16],
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
            'school_id' => 'School ID',
            'name' => 'Name',
            'logo' => 'Logo',
            'address' => 'Address',
            'area_id' => 'Area ID',
            'type' => 'Type',
            'lat' => 'Lat',
            'lon' => 'Lon',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getSchool()
    {
        return $this->hasOne(School::className(), ['id'=>'school_id']);
    }
}
