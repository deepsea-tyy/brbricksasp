<?php

namespace bricksasp\cms\models;

use Yii;

/**
 * This is the model class for table "{{%nav}}".
 *
 * @property int $id
 * @property string|null $code 调用代码
 * @property string|null $name 名称
 * @property string|null $remark 备注
 * @property int|null $status 1启用0关闭
 * @property int|null $user_id
 * @property int|null $owner_id
 * @property int|null $version
 * @property int|null $is_delete
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class Nav extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%nav}}';
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
            [['status', 'user_id', 'owner_id', 'version', 'is_delete', 'created_at', 'updated_at'], 'integer'],
            [['name', 'code'], 'string', 'max' => 32],
            [['remark'], 'string', 'max' => 255],
            [['code'],'unique'],
            ['code', 'validCode'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'name' => 'Name',
            'remark' => 'Remark',
            'status' => 'Status',
            'user_id' => 'User ID',
            'owner_id' => 'Owner ID',
            'version' => 'Version',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
    public function validCode()
    {
        if (!$this->isNewRecord && $this->code && ($this->code != $this->oldAttributes['code'])) {
            $this->addError('code', '编码不可修改');
        }
    }

    public function getMenu()
    {
        return $this->hasMany(NavMenu::className(),['nav_id'=>'id'])->select(['nav_id','target', 'icon','href','name','parent_id']);
    }

    public function saveData($data)
    {
        $this->load($this->formatData($data));
        return $this->save();
    }
}
