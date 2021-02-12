<?php
namespace bricksasp\models\form;

use Yii;
use yii\base\Model;
use Ramsey\Uuid\Uuid;
use bricksasp\models\Mini;
use bricksasp\models\User;
use bricksasp\models\UserFund;
use bricksasp\models\Relation;
use bricksasp\models\UserInfo;
use bricksasp\rbac\components\UserStatus;

/**
 * Register form
 */
class Register extends Model
{
    const TYPE_WX_MINI = 'wx_mini';
    const TYPE_WX_OFFICIAL = 'wx_offi';

    public $username;
    public $access_token;
    public $email;
    public $mobile;
    public $password;
    public $openid;
    public $retypePassword;
    public $current_owner_id;
    public $shop_id;
    public $code;
    public $key;
    public $scene; // Mini::scene类型
    public $type; // Mini::type类型
    public $platform; // Mini::type类型

    /**
     * 使用场景
     */
    public function scenarios()
    {
        return [
            self::TYPE_WX_MINI => ['current_owner_id', 'openid', 'scene', 'mobile','type','platform'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['current_owner_id', 'scene', 'type', 'platform'], 'integer'],
            [['current_owner_id', 'openid', 'scene'], 'required'],
            [['username','openid'], 'string'],
            [['type'], 'validType'],
        ];
    }

    public function validType()
    {
        if ($this->type == Mini::TYPE_WX_MINI) {
            $this->username = 'wx_mini' . Yii::$app->security->generateRandomString(12);
        }elseif ($this->type == Mini::TYPE_WX_OFFICIAL) {
            $this->username = 'wx_offi' . Yii::$app->security->generateRandomString(12);
        }
        // if ($this->type == 2) {
        //     $this->access_token = Yii::$app->security->generateRandomString();
        // }
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if ($this->validate()) {
            $user = new User();
            $user->username = $this->username;
            $user->status = UserStatus::ACTIVE;
            $user->password_hash = Yii::$app->security->generatePasswordHash($this->password ??$this->username);
            $user->auth_key =Yii::$app->security->generateRandomString();
            $user->mobile =$this->mobile;
            $user->access_token = $this->access_token;
            $transaction = UserInfo::getDb()->beginTransaction();
            try {
                if (!$user->save()) {
                    $this->setErrors($user->errors);
                    return null;
                }
                
                $uuid = Uuid::uuid6();
                $userInfo = new UserInfo();
                $userInfo->load([
                    'user_id'=>$user->id,
                    'owner_id'=>$this->current_owner_id,
                    'uuid'=>str_replace('-', '', $uuid->toString()),
                    'openid'=>$this->openid,
                    'scene'=>$this->scene,
                    'platform'=>$this->platform,
                ]);

                $userFund = new UserFund();
                $userFund->load(['user_id'=>$user->id]);
                
                $relation = new Relation();
                $relation->load(['user_id'=>$user->id]);
                
                if (!$userInfo->save() || !$userFund->save() || !$relation->save()) {
                    $transaction->rollBack();
                    return null;
                }
                $transaction->commit();
                return $userInfo;
            }catch(\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
        }
        return null;
    }

    protected function setErrors(array $errors)
    {
        $this->errors = $errors;
    }
}
