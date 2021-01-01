<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%order_runerrands}}".
 *
 * @property int|null $order_id
 * @property string|null $content
 * @property string|null $start_place 起始地
 * @property string|null $end_place 目的地
 * @property string|null $time 办事时间
 * @property int|null $weight 重量
 * @property int|null $gender
 * @property int|null $overtime 超时 小时
 * @property float|null $tip 小费
 */
class OrderRunerrands extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_runerrands}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'weight', 'gender', 'overtime'], 'integer'],
            [['content'], 'string'],
            [['tip'], 'number'],
            [['start_place', 'end_place'], 'string', 'max' => 128],
            [['time'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'order_id' => 'Order ID',
            'content' => 'Content',
            'start_place' => 'Start Place',
            'end_place' => 'End Place',
            'time' => 'Time',
            'weight' => 'Weight',
            'gender' => 'Gender',
            'overtime' => 'Overtime',
            'tip' => 'Tip',
        ];
    }

    public function saveData($data)
    {
        $this->load($this->formatData($data));
        return $this->save();
    }
}
