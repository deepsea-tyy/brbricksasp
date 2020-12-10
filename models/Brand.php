<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%brand}}".
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $owner_id
 * @property string|null $name
 * @property string|null $logo
 * @property int|null $sort
 * @property int|null $status 1显示2不显示
 * @property int|null $is_delete
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class Brand extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%brand}}';
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
            [['user_id', 'owner_id', 'sort', 'status', 'is_delete', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 64],
            [['logo'], 'string', 'max' => 255],
            [['status'], 'default', 'value' => 1],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'owner_id' => 'Owner ID',
            'name' => 'Name',
            'logo' => 'Logo',
            'sort' => 'Sort',
            'status' => 'Status',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getFile()
    {
        return $this->hasOne(File::className(), ['id' => 'logo'])->select(['id','file_url']);
    }

    public function saveData($data)
    {
        $this->load($this->formatData($data));
        return $this->save();
    }
}
