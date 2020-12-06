<?php
namespace bricksasp\cms\models;

use Yii;
use yii\base\Model;
use bricksasp\base\Tools;
use yii\data\ActiveDataProvider;
use bricksasp\cms\models\Article;

/**
 * ArticleSearch represents the model behind the search form of `bricksasp\cms\models\Article`.
 */
class ArticleSearch extends Article
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'owner_id', 'cat_id', 'parent_id', 'type', 'is_comment', 'is_top', 'is_recommend', 'release_at', 'updated_at'], 'integer'],
            [['keywords', 'reprint_info', 'title', 'brief', 'content'], 'safe'],
            ['release_at', 'default', 'value' => time()],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params,$fields=[])
    {
        $query = Article::find()->with($params['with']??[])->select($fields);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'updated_at' => SORT_DESC,
                ]
            ],
        ]);

        if (!empty($params['code'])) {
            $category = ArticleCategory::find()->where(['code' => $params['code']])->one();
            $params['cat_id'] = $category->id;
        }

        $this->load($params);

        if (!$this->validate()) {
            Tools::breakOff(Yii::t('messages',50006));
        }

        if ($params['current_login_type'] == Token::TOKEN_TYPE_FRONTEND && empty($params['user_data'])) {
            $query->andFilterWhere(['status' => 1]);
        }
        // grid filtering conditions
        $query->andFilterWhere([
            'user_id' => $this->user_id,
            'owner_id' => $this->owner_id,
            'cat_id' => $this->cat_id,
            'parent_id' => $this->parent_id,
            'type' => $this->type,
            'is_comment' => $this->is_comment,
            'is_top' => $this->is_top,
            'is_recommend' => $this->is_recommend,
            'status' => $this->status,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'keywords', $this->keywords])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['<=', 'release_at', $this->release_at])
            ->andFilterWhere(['is_delete'=> empty($params['is_delete']) ? 0 : 1]);

        return $dataProvider;
    }
}
