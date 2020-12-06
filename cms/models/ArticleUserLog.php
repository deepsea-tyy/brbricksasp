<?php

namespace bricksasp\cms\models;

use Yii;

/**
 * This is the model class for table "{{%article_user_log}}".
 *
 * @property int|null $user_id
 * @property int|null $article_id
 * @property int|null $created_at
 */
class ArticleUserLog extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%article_user_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'article_id', 'created_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'article_id' => 'Article ID',
            'created_at' => 'Created At',
        ];
    }
}
