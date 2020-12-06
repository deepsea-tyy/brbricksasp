<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%file}}".
 *
 * @property string $id
 * @property int|null $owner_id
 * @property int|null $user_id
 * @property string|null $name
 * @property int|null $photo_width
 * @property int|null $photo_hight
 * @property int|null $file_size
 * @property string|null $file_url
 * @property string|null $mime
 * @property string|null $ext
 * @property int|null $is_delete
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class File extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%file}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['owner_id', 'user_id', 'photo_width', 'photo_hight', 'file_size', 'is_delete', 'created_at', 'updated_at'], 'integer'],
            [['id', 'name'], 'string', 'max' => 64],
            [['file_url', 'mime'], 'string', 'max' => 255],
            [['ext'], 'string', 'max' => 32],
            [['id'], 'unique'],
        ];
    }

    public function behaviors()
    {
        return [
            \yii\behaviors\TimestampBehavior::className(),
            [
                'class' => \bricksasp\common\SnBehavior::className(),
                'attribute' => 'id',
                'type' => \bricksasp\common\SnBehavior::SN_FILE,
            ],
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
            'name' => 'Name',
            'photo_width' => 'Photo Width',
            'photo_hight' => 'Photo Hight',
            'file_size' => 'File Size',
            'file_url' => 'File Url',
            'mime' => 'Mime',
            'ext' => 'Ext',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
