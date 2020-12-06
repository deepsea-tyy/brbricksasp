<?php

namespace bricksasp\cms\models;

use Yii;
use bricksasp\base\Tools;

/**
 * This is the model class for table "{{%article_category}}".
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $owner_id
 * @property int|null $parent_id
 * @property string|null $name
 * @property int|null $status
 * @property int|null $sort
 * @property int|null $version
 * @property string|null $image
 * @property string|null $code
 * @property int|null $is_delete
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class ArticleCategory extends \bricksasp\base\BaseActiveRecord
{
    const STATUS_ON = 1; //启用
    const STATUS_OFF = 0;//关闭
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%article_category}}';
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
            [['name'], 'required'],
            [['user_id', 'owner_id', 'parent_id', 'status', 'version', 'sort', 'is_delete', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 64],
            [['image'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 32],
            [['code'], 'unique'],
            [['parent_id', 'version'], 'default', 'value' => 0],
            [['status'], 'default', 'value' => self::STATUS_ON],
            [['code'], 'default', 'value' => Tools::random_str(Yii::$app->security->generateRandomString(),6)]
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
            'parent_id' => 'Parent ID',
            'name' => 'Name',
            'status' => 'Status',
            'sort' => 'Sort',
            'image' => 'Image',
            'code' => 'Code',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
    
    /**
     * 级联详情
     * @param  intger $id 分类id
     * @return array
     */
    public function cascader(int $id)
    {
        $row = [];
        $model = self::findOne($id);
        if (!$model) return $row;
        
        if ($model->parent_id) {
            $row[] = $model->toArray();
            $row = array_merge($this->cascader($model->parent_id), $row);
        }else{
            $row[] = $model->toArray();
        }
        return $row;
    }

    public function saveData($data)
    {
        
        $this->load($this->formatData($data));
        return $this->save();
    }
}
