<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%school}}".
 *
 * @property int $id
 * @property string|null $name 学校名称
 * @property int|null $parent_id
 * @property string|null $code 学校标识码
 * @property int|null $level 1本科2专科
 * @property int|null $p_id 省
 * @property int|null $c_id 市
 * @property int|null $a_id 区
 * @property string|null $address
 * @property string|null $logo
 * @property string|null $mark
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class School extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%school}}';
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
            [['parent_id', 'level', 'p_id', 'c_id', 'a_id', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 32],
            [['code', 'mark'], 'string', 'max' => 16],
            [['address'], 'string', 'max' => 255],
            [['logo'], 'string', 'max' => 64],
            [['parent_id'], 'default', 'value' => 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'parent_id' => 'Parent ID',
            'code' => 'Code',
            'level' => 'Level',
            'a_id' => 'a_id',
            'address' => 'Address',
            'logo' => 'Logo',
            'mark' => 'Mark',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getArea()
    {
        return $this->hasMany(School::className(),['parent_id'=>'id'])->with(['p','c','a']);
    }

    public function getSchool()
    {
        return $this->hasOne(School::className(),['id'=>'parent_id']);
    }

    public function getP()
    {
        return $this->hasOne(Region::className(),['id'=>'p_id']);
    }

    public function getC()
    {
        return $this->hasOne(Region::className(),['id'=>'c_id']);
    }

    public function getA()
    {
        return $this->hasOne(Region::className(),['id'=>'a_id']);
    }

    public function saveData($data)
    {
        $this->load($this->formatData($data));
        return $this->save();
    }
}
