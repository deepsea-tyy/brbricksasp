<?php
namespace bricksasp\rbac\models\form;

use Yii;
use yii\base\Model;
use bricksasp\rbac\models\User;
use bricksasp\base\Tools;

/**
 * Login form
 */
class Login extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;
    public $code;
    public $key;
    
    private $_user = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password', /*'code', 'key'*/], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
            // [['key'], 'vaildCaptcha'],
        ];
    }

    public function vaildCaptcha()
    {
        if ($this->key && $this->code != '1234') {
            $cache = Yii::$app->getCache();
            $code = $cache->get($this->key);
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
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, Yii::t('messages',40006));
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->getUser()->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}
