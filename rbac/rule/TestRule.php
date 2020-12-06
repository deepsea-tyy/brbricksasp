<?php
namespace bricksasp\rbac\rule;

/**
 * 权限规则列子
 */
class TestRule extends \yii\rbac\Rule
{
    /**
     * @var string name of the rule
     */
    public $name;
    /**
     * @var int UNIX timestamp representing the rule creation time
     */
    public $createdAt;
    /**
     * @var int UNIX timestamp representing the rule updating time
     */
    public $updatedAt;


    /**
     * Executes the rule.
     *
     * @param string|int $user the user ID. This should be either an integer or a string representing
     * the unique identifier of a user. See [[\yii\web\User::id]].
     * @param Item $item the role or permission that this rule is associated with
     * @param array $params parameters passed to [[CheckAccessInterface::checkAccess()]].
     * @return bool a value indicating whether the rule permits the auth item it is associated with.
     */
    public function execute($user, $item, $params){
    	return true;
    }
}