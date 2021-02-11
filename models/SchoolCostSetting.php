<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "basp_school_cost_setting".
 *
 * @property int|null $school_id
 * @property int|null $is_lunch_cost
 * @property int|null $is_dinner_cost
 * @property int|null $is_weather_cist
 * @property int|null $is_difficulty_cost
 */
class SchoolCostSetting extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'basp_school_cost_setting';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['school_id', 'is_lunch_cost', 'is_dinner_cost', 'is_weather_cist', 'is_difficulty_cost'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'school_id' => 'School ID',
            'is_lunch_cost' => 'Is Lunch Cost',
            'is_dinner_cost' => 'Is Dinner Cost',
            'is_weather_cist' => 'Is Weather Cist',
            'is_difficulty_cost' => 'Is Difficulty Cost',
        ];
    }
}
