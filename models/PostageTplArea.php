<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%postage_tpl_area}}".
 *
 * @property int|null $postage_id
 * @property string|null $first 首重/件
 * @property float|null $first_price
 * @property string|null $renew 续重/件
 * @property float|null $renew_price
 * @property float|null $full_price 邮费满额包邮价
 * @property string|null $area_id
 */
class PostageTplArea extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%postage_tpl_area}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['postage_id'], 'integer'],
            [['first_price', 'renew_price', 'full_price'], 'number'],
            [['area_id'], 'string'],
            [['first', 'renew'], 'string', 'max' => 8],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'postage_id' => 'Postage ID',
            'first' => 'First',
            'first_price' => 'First Price',
            'renew' => 'Renew',
            'renew_price' => 'Renew Price',
            'full_price' => 'Full Price',
            'area_id' => 'Area ID',
        ];
    }
}
