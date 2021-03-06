<?php
namespace bricksasp\base;

/**
 * This is the model class for Module form validate.
 */
class FormValidate extends \yii\base\DynamicModel {
	protected $_attributes = [];

	public function load($data, $formName = '') {
		return parent::load($data, $formName);
	}

	public function __construct(array $attributes = [], $config = []) {
		if (!empty($config['scenario'])) {
            $this->setScenario($config['scenario']);
            $scenarios = $this->scenarios();
			foreach ($scenarios[$config['scenario']] as $v) {
				if (empty($attributes[$v])) {
					$attributes[$v] = null;
				}
			}
		}

		foreach ($attributes as $name => $value) {
			if (is_int($name)) {
				$this->_attributes[$value] = null;
			} else {
				$this->_attributes[$name] = $value;
			}
		}

		parent::__construct(array_merge($attributes, $config));
	}

    public function checkArray($data=[], $fields=[])
    {
        foreach ($fields as $f) {
            if (!is_array($data[$f]??[])) {
                $this->addError($f,'只能是数组且不能为空');
                return false;
            }
        }
        return true;
    }

    /**
     * 获取当前场景所需参数
     * @return array
     */
    public function getSaveData()
    {
    	$data = [];
    	$scenarios = $this->scenarios();
    	foreach ($this->attributes as $field => $val) {
    		if (in_array($field, $scenarios[$this->scenario])) {
    			$data[$field] = $val;
    		}
    	}
    	return $data;
    }
}