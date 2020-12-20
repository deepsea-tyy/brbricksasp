<?php

namespace bricksasp\models;

use Yii;
use bricksasp\base\Tools;

/**
 * This is the model class for table "{{%postage_template}}".
 *
 * @property int $id
 * @property int|null $owner_id
 * @property int|null $user_id
 * @property string|null $name 配送方式名称
 * @property int|null $is_default 1默认
 * @property int|null $billing_plan 1按重量计费2按件计费
 * @property int|null $logistics_id 物流公司id
 * @property int|null $special_type 1不配送区域2只配送区域
 * @property string|null $special_area_id
 * @property int|null $special_status 1启用
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class PostageTemplate extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%postage_template}}';
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
            [['owner_id', 'user_id', 'is_default', 'billing_plan', 'logistics_id', 'special_type', 'special_status', 'is_delete', 'created_at', 'updated_at'], 'integer'],
            [['special_area_id'], 'string'],
            [['name'], 'string', 'max' => 32],
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
            'name' => 'Name',
            'is_default' => 'Is Default',
            'billing_plan' => 'Billing Plan',
            'logistics_id' => 'Logistics ID',
            'special_type' => 'Special Type',
            'special_area_id' => 'Special Area ID',
            'special_status' => 'Special Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getTplArea()
    {
        return $this->hasMany(PostageTplArea::className(),['postage_id'=>'id']);
    }

    public function saveData($data)
    {
        if (!$this->checkArray($data,['tpl_area_items'])) {
            return false;
        }

        $this->load($this->formatData($data));
        $transaction = self::getDb()->beginTransaction();
        try {
            if ($this->save() === false) {
                $transaction->rollBack();
                return false;
            }
            if (!empty($data['tpl_area_items'])) {
                $tpl_areas = [];
                foreach ($data['tpl_area_items'] as $k => $v) {
                    $v['postage_id'] = $this->id;
                    if (!$this->checkArray($v,['area_id'])) {
                        $transaction->rollBack();
                        return false;
                    }
                    $v['area_id'] = json_encode($v['area_id'], JSON_UNESCAPED_UNICODE);
                    $tpl_areas[] = $v;
                }

                PostageTplArea::deleteAll(['postage_id'=>$this->id]);
                PostageTplArea::getDb()->createCommand()
                ->batchInsert(PostageTplArea::tableName(),array_keys(end($tpl_areas)),$tpl_areas)
                ->execute();
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
