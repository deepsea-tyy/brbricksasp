<?php

namespace bricksasp\cms\models;

use Yii;

/**
 * This is the model class for table "{{%nav_menu}}".
 *
 * @property int $id
 * @property int|null $nav_id 导航 id
 * @property int|null $parent_id 父 id
 * @property int|null $status 状态;1:显示;0:隐藏
 * @property int|null $sort 排序
 * @property string|null $name 菜单名称
 * @property string|null $target 打开方式
 * @property string|null $href 链接
 * @property string|null $icon 图标
 * @property int|null $owner_id
 * @property int|null $user_id
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class NavMenu extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%nav_menu}}';
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
            [['nav_id', 'parent_id', 'status', 'sort', 'owner_id', 'user_id', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 64],
            [['target', 'icon'], 'string', 'max' => 16],
            [['href'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nav_id' => 'Nav ID',
            'parent_id' => 'Parent ID',
            'status' => 'Status',
            'sort' => 'Sort',
            'name' => 'Name',
            'target' => 'Target',
            'href' => 'Href',
            'icon' => 'Icon',
            'owner_id' => 'Owner ID',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
