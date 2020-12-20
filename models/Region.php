<?php
namespace bricksasp\models;

use bricksasp\base\Tools;

/**
 * This is the model class for table "{{%region}}".
 */
class Region extends \bricksasp\base\BaseActiveRecord {
	/**
	 * {@inheritdoc}
	 */
	public static function tableName() {
		return '{{%region}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules() {
		return [
			[['code'], 'integer'],
			[['name', 'province', 'city', 'area', 'town'], 'string', 'max' => 32],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels() {
		return [
			'id' => 'ID',
			'code' => 'Code',
			'name' => 'Name',
			'province' => 'Province',
			'city' => 'City',
			'area' => 'Area',
			'town' => 'Town',
		];
	}

    /**
     * 级联详情
     * @param  intger $id 分类id
     * @return array
     */
    public function cascader($id,$data=[])
    {
    	if (empty($id)) {
    		return $data;
    	}

        $model = self::find()->where(['id' => $id])->asArray()->one();
        if ($model) {
            return $this->cascader($model['parent_id'],array_merge([$model], $data));
        }
        return $data;
    }


	/**
	 * 获取地区树
	 */
	public static function tree() {

		$data = self::find()->limit(1000)->asArray()->all();
		return Tools::build_tree($data, $root_id = 0, $relation = 'parent_id', $key = 'id', $childname = 'children');
	}

	/**
	 * 设置级联关系
	 */
	public static function setPid($pid = 0, $condition = ['city' => 0, 'id' => 3096]) //2198
	{
//		var_dump($pid);
		$data = Region::find()->select(['id', 'name', 'province', 'city', 'area', 'town'])->where($condition)->all();
		unset($condition['id']);
		foreach ($data as $item) {
			if ($item->city == 0 && $item->area == 0 && $item->town == 0) {
				//province
				Region::updateAll(['parent_id' => $pid], $condition);
				print_r("province---");
				if (in_array($item->id, [1, 18, 787, 2226, 3217, 3218])) {
					$c = ['and', ['province' => $item->province, 'town' => 0], ['!=', 'area', 0]];
					self::setPid($item->id, $c);
				} else {
					$c = ['and', [
							'or', 
							['province' => $item->province, 'area' => 0, 'town' => 0], 
							['province' => $item->province, 'city' => 90, 'town' => 0]
						], ['!=', 'city', 0]];
						// echo "<pre>";
						// print_r(Region::find()->select(['id', 'name', 'province', 'city', 'area', 'town'])->where($c)->asArray()->all());exit();
					self::setPid($item->id, $c);
				}

			}
			if ($item->city != 0 && (($item->area == 0 && $item->town == 0) || ($item->city == 90 && $item->town == 0))) {
				//city
				print_r("city---");
				Region::updateAll(['parent_id' => $pid], $condition);
				$c = ['and', ['province' => $item->province, 'city' => $item->city, 'town' => 0], ['!=', 'area', 0]];
				if (Region::find()->where($c)->asArray()->one()) {
					// echo "11111";
					self::setPid($item->id, $c);
				}else{
					// echo "2222";
					// 没有跳到下一级
					if ($item->city == 90) {
						$c = ['and', ['province' => $item->province, 'city' => $item->city, 'area' => $item->area], ['!=', 'town', 0]];
					}else{
						$c = ['and', ['province' => $item->province, 'city' => $item->city], ['!=', 'town', 0]];
					}
					self::setPid($item->id, $c);
				}
			}
			if ($item->city != 0 && $item->area != 0 && $item->town == 0) {
				//area
				Region::updateAll(['parent_id' => $pid], $condition);
				print_r("area---");
				$c = ['and', ['province' => $item->province, 'city' => $item->city, 'area' => $item->area], ['!=', 'town', 0]];
				self::setPid($item->id, $c);
			}
			if ($item->town != 0) {
				//town
				Region::updateAll(['parent_id' => $pid], $condition);
			}
		}

		return 'ok';
	}

}
