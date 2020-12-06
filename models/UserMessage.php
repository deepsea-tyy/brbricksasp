<?php

namespace bricksasp\models;

use Yii;
use bricksasp\cms\models\Article;

/**
 * This is the model class for table "byn_user_message".
 *
 * @property int $id
 * @property int|null $user_id -1:群发
 * @property int|null $owner_id
 * @property string|null $content 消息内容
 * @property int|null $status 0未读1已读2用户删除
 * @property int|null $type 1默认类型2公告:content为公告id 3推送消息 4用户已读信息
 * @property int|null $user_identify 用户身份 1普通用户2渠道3其他 
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class UserMessage extends \bricksasp\base\BaseActiveRecord
{
    const USER_IDENTIFY_DEFAULT = 1;
    const USER_IDENTIFY_CHANNEL = 2;
    const TYPE_DEFAULT = 1;
    const TYPE_NOTICE = 2; // 公告
    const TYPE_MSG_PUSH = 3; //推送消息
    const TYPE_MSG_READ = 4; // 已读
    const STATUS_DEFAULT = 0; // 未读

    const STATUS_READ = 1; // 已读
    const STATUS_DELETE = 2; //删除
    const GROUP_SENDING = -1; //群发

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'byn_user_message';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            \yii\behaviors\TimestampBehavior::className(),
            // \bricksasp\common\UserIdBehavior::className(),
            \bricksasp\common\OwnerIdBehavior::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['content', 'user_id'], 'required'],
            [['user_id', 'owner_id', 'status', 'type', 'user_identify', 'created_at', 'updated_at'], 'integer'],
            [['content'], 'safe'],
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
            'content' => 'Content',
            'status' => 'Status',
            'type' => 'Type',
            'user_identify' => 'User Identify',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * 消息
     * @return [type] [description]
     */
    public function getMsgStatus(){
        return $this->hasOne(UserMessage::className(),['content'=>'id'])->andWhere(['type'=>self::TYPE_MSG_READ])->select(['content', 'status']);
    }

    /**
     * 公告文章
     * @return [type] [description]
     */
    public function getNotice(){
        return $this->hasOne(Article::className(),['id'=>'content'])->select(['content', 'id']);
    }
}
