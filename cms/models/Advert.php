<?php

namespace bricksasp\cms\models;

use Yii;
use bricksasp\models\File;
use bricksasp\spu\models\Goods;
use bricksasp\spu\models\Product;

/**
 * This is the model class for table "{{%advert}}".
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $owner_id
 * @property int|null $version
 * @property string|null $name
 * @property string|null $image
 * @property string|null $content
 * @property int|null $type 1:url  2:商品  3:文章
 * @property int|null $start_at
 * @property int|null $end_at
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class Advert extends \bricksasp\base\BaseActiveRecord
{
    const TIME_NEVER_EXPIRES = -1; // end_at 永久有效
    const TYPE_ARTICLE = 1; //文章
    const TYPE_URL = 2; // 地址
    const TYPE_GOODS = 3; // 商品
    const TYPE_STORE = 4; // 商铺

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%advert}}';
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
            [['name','type','start_at','end_at','content'], 'required'],
            [['user_id', 'owner_id', 'version', 'type', 'start_at', 'end_at', 'is_delete', 'created_at', 'updated_at'], 'integer'],
            [['name',], 'string', 'max' => 64],
            [['content', 'image'], 'string', 'max' => 255],
            [['end_at'], 'checkValue'],
            [['name'], 'unique', 'message' => '已存在该标题的广告'],
            [['type'], 'default', 'value' => 1],
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
            'name' => '名称',
            'image' => 'Image',
            'content' => 'Content',
            'type' => 'Type',
            'start_at' => 'Start At',
            'end_at' => 'End At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }


    /**
     * 检测开始时间和介绍时间
     */
    public function checkValue()
    {
        if ($this->end_at <= $this->start_at) $this->addError('end_at', '结束时间必须大于开始时间');
    }

    public function getFile()
    {
        return $this->hasOne(File::className(),['id'=>'image']);
    }

    public function getArticle()
    {
        return $this->hasOne(Article::className(),['id'=>'content'])->select(['id','title','author','brief','keywords','content','created_at']);
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(),['id'=>'content'])->select(['id','name','subtitle','image','owner_id','brief','price','sell_count','status','is_on_shelves']);
    }

    public function getProduct(){
        return $this->hasOne(Product::className(),['goods_id'=>'content'])->where(['is_default'=>Product::PRODUCT_IS_DEFAULT_YES]);
    }
    
    public function getAdvertRelation()
    {
        return $this->hasMany(AdvertRelation::className(), ['advert_id' => 'id']);
    }

    public function getPositionItems()
    {
        return $this->hasMany(AdvertPosition::className(), ['id' => 'position_id'])->via('advertRelation');
    }

    public function saveData($data)
    {
        $this->load($this->formatData($data));

        $transaction = self::getDb()->beginTransaction();
        try {
            if ($this->save() === false) {
                $transaction->rollBack();
                return false;
            }

            $adverts = [];
            foreach ($data['position']??[] as $k => $v) {
                $advert['position_id'] = $v;
                $advert['advert_id'] = $this->id;
                $advert['sort'] = $k + 1;
                $adverts[] = $advert;
            }

            AdvertRelation::deleteAll(['advert_id'=>$this->id]);
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
}
