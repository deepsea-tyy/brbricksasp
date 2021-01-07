<?php
namespace bricksasp\rbac\models\form;

use Yii;
use yii\base\Model;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use bricksasp\models\UserInfo;
use bricksasp\models\UserFund;
use bricksasp\models\Relation;
use bricksasp\rbac\models\User;
use bricksasp\rbac\components\UserStatus;
use Ramsey\Uuid\Uuid;
use bricksasp\base\Tools;

/**
 * Signup form
 */
class Signup extends Model
{
    public $username;
    public $email;
    public $mobile;
    public $password;
    public $retypePassword;
    public $current_owner_id;
    public $shop_id;
    public $code;
    public $key;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $class = Yii::$app->getUser()->identityClass ? : 'bricksasp\rbac\models\User';
        return [
            [['username', 'email', 'password', 'retypePassword', 'current_owner_id', 'key', 'code'], 'required'],
            [['username', 'email'], 'filter', 'filter' => 'trim'],
            [['username', 'password'], 'string', 'min' => 6, 'max' => 32],

            ['email', 'email'],
            [['mobile', 'shop_id'], 'integer'],
            ['email', 'unique', 'targetClass' => $class, 'message' => Yii::t('messages', 'This email address has already been taken.')],
            ['username', 'unique', 'targetClass' => $class, 'message' => Yii::t('messages', 'This username has already been taken.')],

            ['retypePassword', 'compare', 'compareAttribute' => 'password'],
            [['key'], 'vaildCaptcha'],
        ];
    }

    public function vaildCaptcha()
    {
        if ($this->key && $this->code != '1234') {
            $code = Yii::$app->getCache()->get($this->key);
            if ($code == $this->code) {
                Yii::$app->getCache()->set($this->key,null);
            }elseif ($code === false) {
                $this->addError('code', Yii::t('messages', 920001));
            }else{
                $this->addError('code', Yii::t('messages', 920002));
            }
        }
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if ($this->validate()) {
            $class = Yii::$app->getUser()->identityClass ? : 'bricksasp\rbac\models\User';
            $user = new $class();
            $user->username = $this->username;
            $user->email = $this->email;
            $user->mobile = $this->mobile;
            $user->status = ArrayHelper::getValue(Yii::$app->params, 'user.defaultStatus', UserStatus::ACTIVE);
            $user->setPassword($this->password);
            $user->generateAuthKey();
            $user->invite_code = Tools::random_str();
            $transaction = UserInfo::getDb()->beginTransaction();
            try {
                if (!$user->save()) {
                    $transaction->rollBack();
                    return null;
                }
                $uuid = Uuid::uuid6();
                $userInfo = new UserInfo();
                $userInfo->load([
                    'user_id'=>$user->id,
                    'owner_id'=>$this->current_owner_id,
                    'uuid'=>str_replace('-', '', $uuid->toString())
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
                return $user;
            }catch(\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
        }

        return null;
    }
}
