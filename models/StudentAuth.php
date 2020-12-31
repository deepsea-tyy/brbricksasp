<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%student_auth}}".
 *
 * @property int $owner_id
 * @property int $user_id
 * @property int|null $school_id 主校id
 * @property int|null $school_area_id 校区id
 * @property string|null $faculty 院系
 * @property string|null $subject 专业
 * @property int|null $enrollment_at 入学时间
 * @property int|null $student_id 学号
 * @property string|null $student_id_card_frontal_photo 学生证正面照
 * @property string|null $student_id_card_reverse_photo 学生证反面照
 * @property int|null $status 0未审核1通过2拒绝
 * @property string|null $refuse_reasons 拒绝原因
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class StudentAuth extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%student_auth}}';
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
            [['owner_id', 'user_id', 'school_id', 'school_area_id', 'enrollment_at', 'student_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['faculty', 'subject'], 'string', 'max' => 32],
            [['student_id_card_frontal_photo', 'student_id_card_reverse_photo'], 'string', 'max' => 64],
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
            'school_id' => 'School ID',
            'school_area_id' => 'School Area ID',
            'faculty' => 'Faculty',
            'subject' => 'Subject',
            'enrollment_at' => 'Enrollment At',
            'student_id' => 'Student ID',
            'student_id_card_frontal_photo' => 'Student Id Card Frontal Photo',
            'student_id_card_reverse_photo' => 'Student Id Card Reverse Photo',
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
