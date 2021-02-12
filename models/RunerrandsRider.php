<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "basp_runerrands_rider".
 *
 * @property int|null $user_id
 * @property int|null $school_id
 * @property int|null $school_area_id
 * @property string|null $name
 * @property string|null $phone
 * @property int|null $has_car
 * @property int|null $status
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

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'school_id', 'school_area_id', 'has_car', 'status', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 8],
            [['phone'], 'string', 'max' => 11],
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
            'name' => 'Name',
            'phone' => 'Phone',
            'has_car' => 'Has Car',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
