<?php
namespace bricksasp\rbac\models\form;

use Yii;
use bricksasp\rbac\models\User;
use yii\base\Model;

/**
 * Description of ChangePassword
 *
 * @author 649909457@qq.com
 * @since 1.0
 */
class ChangePassword extends Model
{
    public $oldPassword;
    public $newPassword;
    public $retypePassword;
    public $_user;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['oldPassword', 'newPassword', 'retypePassword'], 'required'],
            [['oldPassword'], 'validatePassword'],
            [['newPassword'], 'string', 'min' => 6, 'max'=>'32'],
            [['retypePassword'], 'compare', 'compareAttribute' => 'newPassword', 'message'=>'两次密码不一致'],
        ];
    }

    public function init()
    {
        if ($this->_user) {
            return $this->_user;
        }

        $this->_user = User::findOne(Yii::$app->user->identity->id);
        return $this->_user;
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     */
    public function validatePassword()
    {
        if (!$this->_user || !$this->_user->validatePassword($this->oldPassword)) {
            $this->addError('oldPassword', '原密码错误');
        }
    }

    /**
     * Change password.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function change()
    {
        if ($this->validate()) {
            $this->_user->setPassword($this->newPassword);
            $this->_user->generateAuthKey();
            if ($this->_user->save()) {
                return true;
            }
        }

        return false;
    }

    public function attributeLabels()
    {
        return [
            'newPassword' => '新密码',
            'oldPassword' => '原密码',
            'retypePassword' => '确认密码',
        ];
    }
}
