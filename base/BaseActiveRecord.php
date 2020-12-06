<?php
namespace bricksasp\base;

use Yii;
use yii\db\ActiveQuery;
use bricksasp\helpers\Tools;

class BaseActiveRecord extends \yii\db\ActiveRecord
{
    public function load($data,$formName = '')
    {
        return parent::load($data,$formName);
    }

    /*public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if (!$this->isNewRecord && $this->hasAttribute('user_id')) {
            $uid = Yii::$app->getUser()->getId();
            if ($uid && $this->user_id && $this->user_id != $uid) {
                Tools::breakOff(Yii::t('messages',40003));
            }
        }
        return true;
    }

    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        if ($this->hasAttribute('user_id')) {
            $uid = Yii::$app->getUser()->getId();
            if ($this->user_id && $this->user_id != $uid) {
                Tools::breakOff(Yii::t('messages',40003));
            }
        }
        return true;
    }*/

    /**
     * 关联数据排序
     */
    public static function sortItem($data,$sort)
    {
        $items = $data[0];
        $data = array_column($items, $data[1]);
        $sort = array_column($sort[0], $sort[1], $sort[2]);
        $k = [];
        foreach ($data as $v) {
            $k[] = $sort[$v];
        }
        $items = array_combine($k, $items);
        ksort($items);
        return array_values($items);
    }

    public function formatData($data)
    {
        $data['owner_id'] = $data['current_owner_id'];
        $data['user_id'] = $data['current_user_id'];

        $start_at = $end_at = $release_at = null;
        if (!empty($data['start_at']) && !is_numeric($data['start_at'])) {
            $start_at = strtotime($data['start_at']);
            $start_at = $start_at ? $start_at : null;
        }
        if (!empty($data['end_at']) && !is_numeric($data['end_at'])) {
            $end_at = strtotime($data['end_at']);
            $end_at = $end_at ? $end_at : null;
        }
        if (!empty($data['release_at']) && !is_numeric($data['release_at'])) {
            $release_at = strtotime($data['release_at']);
        }

        if (($start_at && !$end_at) || (!$start_at && $end_at)) {
            $start_at = $start_at ? $start_at : $end_at;
            $end_at = $start_at + 3600 * 24 - 1;
        }elseif ($start_at && $end_at) {
            $end_at = $end_at + 3600 * 24 - 1;
        }

        if ($start_at || $release_at) {
            $data['start_at'] = $start_at;
            $data['end_at'] = $end_at;
            $data['release_at'] = $release_at;
        }
        return $data;
    }
    
    public function checkArray($data=[], $fields=[])
    {
        foreach ($fields as $f) {
            if (!is_array($data[$f]??false)) {
                $this->addError($f,'只能是数组');
                return false;
            }
        }
        return true;
    }
}