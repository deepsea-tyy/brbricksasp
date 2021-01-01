<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%company_qualifications}}".
 *
 * @property int $id
 * @property int|null $owner_id
 * @property int|null $user_id
 * @property int|null $type 企业类型1有限责任公司
 * @property string|null $full_address
 * @property string|null $incorporation_at 公司成立日期
 * @property float|null $registered_capital 注册资本
 * @property string|null $business_scope 经营范围
 * @property string|null $credit_code 社会统一信用代码
 * @property string|null $business_license 营业执照扫描件
 * @property string|null $business_license_duration 开始时间戳,结束时间戳
 * @property int|null $three_to_one 1三证合一
 * @property int|null $tax_type 1小规模纳税人
 * @property string|null $legal_rep 法人代表
 * @property string|null $legal_rep_frontal_photo 法人身份证正面照
 * @property string|null $legal_rep_reverse_photo 法人身份证反面照
 * @property string|null $trademark_name 商标名称
 * @property string|null $trademark_license 商标证书
 * @property string|null $trademark_license_duration 商标有效时间
 * @property string|null $trademark_license_change 商标变更/续展证明
 * @property string|null $brand_permit 品牌授权书
 * @property string|null $purchase_sale_voucher 购销凭证
 * @property string|null $administrative_license 行政许可证
 * @property string|null $trademark_inspection_report 检查报告
 * @property string|null $trademark_inspection_report_duration
 * @property string|null $food_business_license 食品经营许可证
 * @property string|null $food_business_license_duration
 * @property string|null $food_inspection_report 食品检测报告
 * @property int|null $food_inspection_report_duration
 * @property int|null $status 0未审核1通过2拒绝
 * @property int|null $is_delete
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class CompanyQualifications extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%company_qualifications}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'owner_id', 'user_id', 'type', 'three_to_one', 'tax_type', 'status', 'is_delete', 'created_at', 'updated_at'], 'integer'],
            [['registered_capital'], 'number'],
            [['business_scope'], 'string'],
            [['full_address'], 'string', 'max' => 255],
            [['credit_code'], 'string', 'max' => 30],
            [['business_license', 'legal_rep_frontal_photo', 'legal_rep_reverse_photo', 'trademark_license', 'trademark_license_change', 'brand_permit', 'purchase_sale_voucher', 'administrative_license', 'trademark_inspection_report', 'food_business_license', 'food_inspection_report'], 'string', 'max' => 64],
            [['business_license_duration', 'trademark_name', 'trademark_license_duration', 'trademark_inspection_report_duration', 'food_business_license_duration', 'food_inspection_report_duration', 'incorporation_at'], 'string', 'max' => 32],
            [['legal_rep'], 'string', 'max' => 4],
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
            'type' => 'Type',
            'full_address' => 'Full Address',
            'incorporation_at' => 'Incorporation At',
            'registered_capital' => 'Registered Capital',
            'business_scope' => 'Business Scope',
            'credit_code' => 'Credit Code',
            'business_license' => 'Business License',
            'business_license_duration' => 'Business License Duration',
            'three_to_one' => 'Three To One',
            'tax_type' => 'Tax Type',
            'legal_rep' => 'Legal Rep',
            'legal_rep_frontal_photo' => 'Legal Rep Frontal Photo',
            'legal_rep_reverse_photo' => 'Legal Rep Reverse Photo',
            'trademark_name' => 'Trademark Name',
            'trademark_license' => 'Trademark License',
            'trademark_license_duration' => 'Trademark License Duration',
            'trademark_license_change' => 'Trademark License Change',
            'brand_permit' => 'Brand Permit',
            'purchase_sale_voucher' => 'Purchase Sale Voucher',
            'administrative_license' => 'Administrative License',
            'trademark_inspection_report' => 'Trademark Inspection Report',
            'trademark_inspection_report_duration' => 'Trademark Inspection Report Duration',
            'food_business_license' => 'Food Business License',
            'food_business_license_duration' => 'Food Business License Duration',
            'food_inspection_report' => 'Food Inspection Report',
            'food_inspection_report_duration' => 'Food Inspection Report Duration',
            'status' => 'Status',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function saveData($data)
    {
        $this->load($this->formatData($data));
        return $this->save();
    }
}
