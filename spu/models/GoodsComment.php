<?php

namespace bricksasp\spu\models;

use Yii;
use bricksasp\base\Tools;
use bricksasp\models\File;
use bricksasp\models\OrderItem;
use bricksasp\models\FileRelation;

/**
 * This is the model class for table "{{%goods_comment}}".
 *
 * @property int $id
 * @property int|null $owner_id
 * @property int|null $user_id
 * @property int|null $parent_id
 * @property int|null $order_item_id
 * @property int|null $goods_id
 * @property int|null $product_id
 * @property int|null $score 评价1-5星
 * @property string|null $content 评价内容
 * @property string|null $seller_content 商家回复
 * @property int|null $status 1显示 2不显示
 * @property int|null $is_delete
 * @property int|null $like 点赞数
 * @property int|null $reply_num 回复数
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class GoodsComment extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_comment}}';
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
            [['goods_id', 'product_id'], 'required'],
            [['owner_id', 'user_id', 'parent_id', 'order_item_id', 'goods_id', 'product_id', 'score', 'status', 'is_delete', 'like', 'reply_num', 'created_at', 'updated_at'], 'integer'],
            [['content', 'seller_content'], 'string'],
            [['status'], 'default', 'value' => 2],
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
            'parent_id' => 'Parent ID',
            'order_item_id' => 'Order Item ID',
            'goods_id' => 'Goods ID',
            'product_id' => 'Product ID',
            'score' => 'Score',
            'content' => 'Content',
            'seller_content' => 'Seller Content',
            'status' => 'Status',
            'is_delete' => 'Is Delete',
            'like' => 'Like',
            'reply_num' => 'Reply Num',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getFileRelation()
    {
        return $this->hasMany(FileRelation::className(), ['object_id' => 'id'])->andWhere(['type'=>FileRelation::TYPE_GOODS_CMT]);
    }

    public function getImages(){
        return $this->hasMany(File::className(),['id'=>'file_id'])->via('fileRelation');
    }

    public function saveData($data)
    {
        if (!$this->checkArray($data,['imageItems'])) {
            return false;
        }
        // print_r($data);exit();
        $this->load($this->formatData($data));

        $transaction = self::getDb()->beginTransaction();
        try {
            if (!$this->save()) {
                $transaction->rollBack();
                return false;
            }
            
            if (!empty($data['order_item_id'])) {
                OrderItem::updateAll(['is_comment'=>OrderItem::COMMENT_YES], ['id'=>$data['order_item_id']]);
            }

            if (!empty($data['imageItems'])) {
                $images = [];
                foreach ($data['imageItems'] as $k => $v) {
                    $image['object_id'] = $this->id;
                    $image['file_id'] = $v;
                    $image['type'] = FileRelation::TYPE_GOODS_CMT;
                    $image['sort'] = $k + 1;
                    $images[] = $image;
                }
                FileRelation::deleteAll(['object_id'=>$this->id,'type'=>FileRelation::TYPE_GOODS_CMT]);
                self::getDb()->createCommand()
                ->batchInsert(FileRelation::tableName(),array_keys(end($images)?end($images):[]),$images)
                ->execute();
            }
                // $transaction->rollBack();
            $transaction->commit();
            return true;
        } catch(\Throwable $e) {
            $transaction->rollBack();
            Tools::breakOff($e->getMessage());
        }
        return $this->save();
    }
}
