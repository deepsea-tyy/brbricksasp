<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%company}}".
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $owner_id
 * @property int|null $parent_id
 * @property string|null $name 公司名称
 * @property string|null $website 官网
 * @property string|null $logo
 * @property string|null $gps json格式1百度2高德3腾讯4百度
 * @property int|null $level 0默认等级
 * @property string|null $mark
 * @property int|null $area_id
 * @property string|null $address
 * @property int|null $status 0待审核1审核通过2审核不能过
 * @property int|null $review_user_id
 * @property int|null $review_at
 * @property int|null $is_delete
 * @property int|null $body 主体1境内企业
 * @property string|null $main_products 主营商品
 * @property int|null $main_model 主营模式1生产厂家
 * @property string|null $director 负责人
 * @property string|null $director_mail 负责人邮箱
 * @property string|null $director_mobile 负责人手机
 * @property string|null $operations_contact 运营联系人
 * @property string|null $operations_contact_mobile 运营联系人手机
 * @property string|null $operations_contact_mail 运营联系人邮箱
 * @property int $created_at
 * @property int $updated_at
 */
class Company extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%company}}';
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
            [['id', 'user_id', 'owner_id', 'parent_id', 'level', 'review_user_id', 'review_at', 'area_id', 'status', 'is_delete', 'body', 'main_model', 'created_at', 'updated_at'], 'integer'],
            [['main_products'], 'string'],
            [['name', 'website', 'logo', 'mark'], 'string', 'max' => 255],
            [['gps', 'address'], 'string', 'max' => 64],
            [['director', 'operations_contact'], 'string', 'max' => 4],
            [['director_mail', 'operations_contact_mail'], 'string', 'max' => 32],
            [['director_mobile', 'operations_contact_mobile'], 'string', 'max' => 20],
            [['id'], 'unique'],
            [['level'], 'default', 'value' => 0],
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
            'website' => 'Website',
            'logo' => 'Logo',
            'gps' => 'Gps',
            'level' => 'Level',
            'review_user_id' => 'Review User ID',
            'review_at' => 'Review At',
            'mark' => 'Mark',
            'area_id' => 'Area ID',
            'address' => 'Address',
            'status' => 'Status',
            'is_delete' => 'Is Delete',
            'body' => 'Body',
            'main_products' => 'Main Products',
            'main_model' => 'Main Model',
            'director' => 'Director',
            'director_mail' => 'Director Mail',
            'director_mobile' => 'Director Mobile',
            'operations_contact' => 'Operations Contact',
            'operations_contact_mobile' => 'Operations Contact Mobile',
            'operations_contact_mail' => 'Operations Contact Mail',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
