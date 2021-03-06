<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%school_relation}}".
 *
 * @property int|null $owner_id
 * @property int|null $object_id
 * @property int|null $type 1学校2校区
 */
class SchoolRelation extends \bricksasp\base\BaseActiveRecord
{
    const TYPE_SCHOOL = 1;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%school_relation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['owner_id', 'object_id', 'type'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'owner_id' => 'Owner ID',
            'object_id' => 'Object ID',
            'type' => 'Type',
        ];
    }
}
