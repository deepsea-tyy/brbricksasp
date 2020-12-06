<?php

namespace bricksasp\cms\models;

use Yii;
use yii\db\Expression;
use bricksasp\base\Tools;
use bricksasp\spu\models\Goods;

/**
 * This is the model class for table "{{%advert_position}}".
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $owner_id
 * @property int|null $version
 * @property string|null $name 幻灯片名称
 * @property string|null $code 广告位置编码
 * @property int|null $is_delete
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class AdvertPosition extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%advert_position}}';
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
            [['name', 'code'], 'required'],
            [['user_id', 'owner_id', 'version', 'is_delete', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 64],
            [['code'], 'string', 'max' => 32],
            [['code'], 'unique'],
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
            'user_id' => 'User ID',
            'owner_id' => 'Owner ID',
            'version' => 'Version',
            'name' => 'Name',
            'code' => '广告位置编码',
            'is_delete' => 'is_delete',
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
    
    public function getAdvertRelation()
    {
        return $this->hasMany(AdvertRelation::className(), ['position_id' => 'id']);
    }

    public function getAdvertItems()
    {
        return $this->hasMany(Advert::className(), ['id' => 'advert_id'])->via('advertRelation');
    }

    public function saveData($data)
    {
        $this->load($this->formatData($data));

        $transaction = self::getDb()->beginTransaction();
        try {
            // 保存广告位
            if ($this->save() === false) {
                $transaction->rollBack();
                return false;
            }

            $adverts = [];
            foreach ($data['advert']??[] as $k => $v) {
                $advert['position_id'] = $this->id;
                $advert['advert_id'] = $v;
                $advert['sort'] = $k + 1;
                $adverts[] = $advert;
            }

            AdvertRelation::deleteAll(['position_id'=>$this->id]);
            AdvertRelation::getDb()->createCommand()
            ->batchInsert(AdvertRelation::tableName(),['position_id','advert_id','sort'],$adverts)
            ->execute();

            $transaction->commit();
            return true;
        } catch(\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        return false;
    }

    /**
     * 
     * 广告位详情
     * @OA\Schema(
     *   schema="AdvertPositionDetail",
     *   description="广告位模型",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="name", type="string", description="广告位名称"),
     *       @OA\Property(property="content", type="string", description="内容"),
     *       @OA\Property(property="type", type="integer", description="1:文章 2:url 3商品 4商铺"),
     *       @OA\Property(property="image", description="图片")
     *     )
     *   }
     * )
     */
    public function detail($params)
    {
        $position = $this::find()->select(['id', 'name'])->where(['code' => $params['code']])->asArray()->one();
        if (!$position) return false;
        $sort = AdvertRelation::find()->where(['position_id' => $position['id']])->all();

        $t = time();
        $advert = Advert::find()
            ->with(['article','file'])
            ->select(['id', 'image', 'name', 'content', 'type','created_at'])
            ->andWhere(['id'=>array_column($sort, 'advert_id')])
            ->andWhere(['<=', 'start_at', $t])
            ->andWhere(['>=', 'end_at', $t])
            ->andWhere(['is_delete'=>0])
            ->orWhere(['end_at'=>Advert::TIME_NEVER_EXPIRES])
            ->all();
        foreach ($advert as $k => $item) {
            $advert[$k] = $item->toArray();
            $advert[$k]['file'] = $item->file ?? [];
            if ($item->type == Advert::TYPE_ARTICLE) {
                $advert[$k]['article'] = $item->article;
                $advert[$k]['article']['keywords'] = explode(',', $item->article->keywords);
            }
            if ($item->type == Advert::TYPE_GOODS) {
                $goods = $item->goods;
                if($goods){
                    $goods = $goods->toArray();
                    if($goods['is_on_shelves'] != Goods::GOODS_ON_SHELVES_YES){
                        if(isset($advert[$k])){
                            unset($advert[$k]);
                        }
                    }
                }
            }
            if ($item->type == Advert::TYPE_STORE) {
                $store = $item->store;
                $distance = 0;
                if($store){
                    $store = $store->toArray();
                    $distance = Tools::distance($store['lat'], $store['lon'], $params['lat'],$params['lon']);
                }

                $advert[$k]['distance'] = round($distance,2);
                $store['distance'] = round($distance,2);
                $store['label'] = $item->storeLabel?$item->storeLabel->name:'';
                $advert[$k]['store'] = $store;
            }
        }
        if($advert){
            $advert = self::sortItem([$advert, 'id'],[$sort, 'sort', 'advert_id']);
        }
        $position['advert'] = $advert;
        return $position;
    }
}
