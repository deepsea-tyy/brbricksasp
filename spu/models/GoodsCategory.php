<?php
namespace bricksasp\spu\models;

use Yii;
use bricksasp\models\File;

/**
 * This is the model class for table "{{%goods_category}}".
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $parent_id
 * @property string|null $name
 * @property int|null $type_id
 * @property int|null $status
 * @property int|null $sort
 * @property string|null $image_id
 * @property int|null $is_delete
 * @property int|null $owner_id
 * @property int|null $version
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class GoodsCategory extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_category}}';
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
            [['user_id', 'parent_id', 'type_id', 'status', 'sort', 'is_delete', 'owner_id', 'version', 'created_at', 'updated_at'], 'integer'],
            [['name', 'image_id'], 'string', 'max' => 64],
            [['status', 'sort'], 'default', 'value' => 1],
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
            'parent_id' => 'Parent ID',
            'name' => 'Name',
            'type_id' => 'Type ID',
            'status' => 'Status',
            'sort' => 'Sort',
            'image_id' => 'Image ID',
            'is_delete' => 'Is Delete',
            'owner_id' => 'Owner ID',
            'version' => 'Version',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getType()
    {
        return $this->hasOne(Type::className(), ['id' => 'type_id'])->select(['name', 'id']);
    }

    public function getFile()
    {
        return $this->hasOne(File::className(), ['id' => 'image_id'])->select(['id', 'file_url']);
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
