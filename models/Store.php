<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%store}}".
 *
 * @property int $owner_id
 * @property int|null $user_id
 * @property int|null $parent_id 0表示主店 其他表示分店
 * @property string|null $name 店铺名称
 * @property int|null $industry_id 行业分类id
 * @property int|null $type 1商铺2供货商3学校站长
 * @property int|null $nature 经营性质1旗舰店2专营店3专卖店
 * @property string|null $brand 经营品牌
 * @property string|null $logo
 * @property string|null $brief
 * @property string|null $contacts 联系人
 * @property string|null $contacts_phone 联系人电话
 * @property int|null $return_area_id
 * @property string|null $return_address 退货地址
 * @property string|null $tm_url 天猫店铺链接
 * @property string|null $jd_url 京东店铺链接
 * @property string|null $own_site_url 自营网站
 * @property int|null $area_id
 * @property string|null $address 店铺地址
 * @property int|null $status 0未审核1通过2拒绝
 * @property string|null $refuse_reasons 拒绝原因
 * @property int|null $is_delete
 * @property int|null $start_at 开始有效时间
 * @property int|null $end_at 结束有效时间
 * @property float|null $total_amount
 * @property float|null $out_amount
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class Store extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%store}}';
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
            [['owner_id'], 'required'],
            [['owner_id', 'user_id', 'parent_id', 'industry_id', 'type', 'nature', 'return_area_id', 'area_id', 'status', 'is_delete', 'start_at','end_at','created_at', 'updated_at'], 'integer'],
            [['total_amount', 'out_amount'], 'number'],
            [['name', 'brand'], 'string', 'max' => 32],
            [['logo', 'return_address', 'address'], 'string', 'max' => 64],
            [['brief', 'tm_url', 'jd_url', 'own_site_url', 'refuse_reasons'], 'string', 'max' => 255],
            [['contacts'], 'string', 'max' => 4],
            [['contacts_phone'], 'string', 'max' => 20],
            [['owner_id'], 'unique'],
            [['parent_id', 'total_amount', 'out_amount'], 'default', 'value' => 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'owner_id' => 'Owner ID',
            'user_id' => 'User ID',
            'name' => 'Name',
            'industry_id' => 'Industry ID',
            'type' => 'Type',
            'nature' => 'Nature',
            'brand' => 'Brand',
            'logo' => 'Logo',
            'brief' => 'Brief',
            'contacts' => 'Contacts',
            'contacts_phone' => 'Contacts Phone',
            'return_area_id' => 'Return Area ID',
            'return_address' => 'Return Address',
            'tm_url' => 'Tm Url',
            'jd_url' => 'Jd Url',
            'own_site_url' => 'Own Site Url',
            'area_id' => 'Area ID',
            'address' => 'Address',
            'status' => 'Status',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['owner_id'=>'owner_id']);
    }

    public function getCompanyQualifications()
    {
        return $this->hasOne(CompanyQualifications::className(), ['owner_id'=>'owner_id']);
    }

    public function getSchoolRelation()
    {
        return $this->hasOne(StoreRelation::className(), ['owner_id'=>'owner_id']);
    }

    public function getSchool()
    {
        return $this->hasOne(School::className(), ['id'=>'object_id'])->select(['id','name'])->via('schoolRelation');
    }

    public function saveData($data)
    {
        if (!$this->checkArray($data,['companyItem','qualificationsItem'])) {
            return false;
        }

        $this->load($this->formatData($data));
        $transaction = self::getDb()->beginTransaction();
        try {
            if ($this->save() === false) {
                $transaction->rollBack();
                return false;
            }
            if (!empty($data['companyItem'])) {
                if ($this->checkArray($data['companyItem'],['gps'])) {
                    $data['companyItem']['gps'] = json_encode( $data['companyItem']['gps']);
                }else{
                    $transaction->rollBack();
                    return false;
                }
                Company::deleteAll(['owner_id'=>$this->owner_id]);
                $data['companyItem']['owner_id'] = $this->owner_id;
                $cmodel = new Company();
                if (!$cmodel->saveData($data['companyItem'])) {
                    $this->setErrors($cmodel->errors);
                    $transaction->rollBack();
                    return false;
                }
            }
            if (!empty($data['qualificationsItem'])) {
                CompanyQualifications::deleteAll(['owner_id'=>$this->owner_id]);
                $data['qualificationsItem']['owner_id'] = $this->owner_id;
                $cqmodel = new CompanyQualifications();
                $cqmodel->load($data['qualificationsItem']);
                if (!$cqmodel->save()) {
                    $this->setErrors($cqmodel->errors);
                    $transaction->rollBack();
                    return false;
                }
            }

            $transaction->commit();
            return true;
        } catch(\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        return false;
    }

}
