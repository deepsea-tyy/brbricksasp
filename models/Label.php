<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%label}}".
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $owner_id
 * @property string|null $name
 * @property string|null $style
 * @property int|null $type 1样式名2原生
 * @property int|null $version
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class Label extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%label}}';
    }

    public function behaviors()
    {
        return [
            \yii\behaviors\TimestampBehavior::className(),
            \bricksasp\common\VersionBehavior::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'style', 'type'], 'required'],
            [['user_id', 'owner_id', 'version', 'is_delete', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 16],
            [['style'], 'string', 'max' => 64],
            [['name'], 'unique', 'message' => '已存在改标签'],
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
            'style' => 'Style',
            'type' => 'Type',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * 
     * @OA\Schema(
     *   schema="label",
     *   description="标签结构",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="name", type="string", description="标签名称"),
     *       @OA\Property(property="style", type="string", description="标签样式/颜色"),
     *       @OA\Property(property="type", type="integer", description="1样式名2原生"),
     *     )
     *   }
     * )
     */
    public function saveData($data)
    {
        $this->load($this->formatData($data));
        return $this->save();
    }
}
