<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "basp_relation".
 *
 * @property int $user_id
 * @property int|null $parent_id
 * @property int|null $recommend_id 推荐人
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class Relation extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%relation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'parent_id', 'recommend_id', 'created_at', 'updated_at'], 'integer'],
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
            'parent_id' => 'Parent ID',
            'recommend_id' => 'Recommend ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
