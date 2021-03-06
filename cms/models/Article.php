<?php
namespace bricksasp\cms\models;

use Yii;
use bricksasp\models\File;
use bricksasp\models\Label;
use bricksasp\models\LabelRelation;

/**
 * This is the model class for table "{{%article}}".
 *
 * @property int $id
 * @property int|null $user_id 发表者id
 * @property int|null $owner_id
 * @property int|null $version
 * @property int|null $cat_id 分类id
 * @property string|null $keywords seo keywords
 * @property string|null $reprint_info 转载文章的来源说明
 * @property string|null $title 文章标题
 * @property string|null $subtitle 副标题
 * @property string|null $author 作者
 * @property string|null $image 封面
 * @property string|null $brief 文章摘要
 * @property string|null $content 文章内容
 * @property int|null $parent_id 文章的父级文章 id,表示文章层级关系
 * @property int|null $type 文章类型，1文章,2页面
 * @property int|null $comments_num 评论数
 * @property int|null $view_num 浏览数
 * @property int|null $like_num 文章赞数
 * @property int|null $is_comment 评论1允许，2不允许
 * @property int|null $is_top 1置顶 2不置顶
 * @property int|null $is_recommend 推荐 1推荐 2不推荐
 * @property int|null $release_at 文章发布日期 可修改，显示给用户
 * @property int|null $status 0未发布 1已发布 2未通过审核
 * @property int|null $is_delete
 * @property int|null $created_at
 * @property int|null $updated_at 
 */
class Article extends \bricksasp\base\BaseActiveRecord
{
    const CAT_DEFAULT = 1; //默认分类
    const CAT_NOTICE = 2; //公告

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%article}}';
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
            [['title', 'content'], 'required'],
            [['user_id', 'owner_id', 'version', 'parent_id', 'type', 'comments_num', 'view_num', 'like_num', 'is_comment', 'is_top', 'is_recommend', 'release_at', 'status', 'is_delete', 'created_at', 'updated_at'], 'integer'],
            [['brief', 'content'], 'string'],
            [['keywords', 'title', 'subtitle', 'image'], 'string', 'max' => 255],
            [['reprint_info'], 'string', 'max' => 150],
            [['author'], 'string', 'max' => 16],
            [['cat_id'], 'validCatid'],
            [['title'], 'unique', 'message' => '已存在该标题的文章'],

            [['status', 'comments_num', 'view_num', 'like_num'], 'default', 'value'=>0],
            [['is_comment', 'type'], 'default', 'value'=>1],
            [['cat_id'], 'default', 'value'=>self::CAT_DEFAULT],
            [['release_at'], 'default', 'value'=>time()],
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
            'cat_id' => 'Cat ID',
            'keywords' => 'Keywords',
            'reprint_info' => 'Reprint Info',
            'title' => 'Title',
            'subtitle' => 'Subtitle',
            'author' => 'Author',
            'image' => 'Image',
            'brief' => 'Brief',
            'content' => 'Content',
            'parent_id' => 'Parent ID',
            'type' => 'Type',
            'comments_num' => 'Comments num',
            'view_num' => 'View num',
            'like_num' => 'Like num',
            'is_comment' => 'Is Comment',
            'is_top' => 'Is Top',
            'is_recommend' => 'Is Recommend',
            'release_at' => 'Release At',
            'status' => 'Status',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function validCatid()
    {
        if ($this->cat_id) {
            $this->cat_id = $this->cat_id[count($this->cat_id)-1]??null;
        }
    }

    public function getLabelItems()
    {
        return $this->hasMany(LabelRelation::className(), ['object_id' => 'id'])->andWhere(['type'=>LabelRelation::TYPE_ARTICLE]);
    }

    public function getLabels()
    {
        return $this->hasMany(Label::className(), ['id' => 'label_id'])->via('labelItems')->select(['id', 'name', 'style', 'type']);
    }

    public function getFile()
    {
        return $this->hasMany(File::className(), ['id' => 'image']);
    }

    public function getCommentItems()
    {
        return $this->hasMany(ArticleComment::className(), ['article_id' => 'id'])/*->onCondition(['cat_id' => 1])*/;
    }

    public function saveData($data)
    {
        if (!$this->checkArray($data,['cat_id', 'labels'])) {
            return false;
        }

        $this->load($this->formatData($data));
        $transaction = self::getDb()->beginTransaction();
        try {
            if ($this->save() === false) {
                $transaction->rollBack();
                return false;
            }
            if (!empty($data['labels'])) {
                $labels = [];
                foreach ($data['labels'] as $k => $v) {
                    $label['article_id'] = $this->id;
                    $label['label_id'] = $v;
                    $label['sort'] = $k + 1;
                    $labels[] = $label;
                }

                ArticleLabel::deleteAll(['article_id'=>$this->id]);
                ArticleLabel::getDb()->createCommand()
                ->batchInsert(ArticleLabel::tableName(),array_keys(end($labels)),$labels)
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
