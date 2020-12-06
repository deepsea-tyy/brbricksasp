<?php
namespace bricksasp\common;

use Yii;
use Detection\MobileDetect;

class DeviceDetect extends \yii\base\Component {
	/**
	* @var MobileDetect
	*/
	private $_mobileDetect;

	public function __call($name, $parameters) {
		return call_user_func_array(
			array($this->_mobileDetect, $name),
			$parameters
		);
	}

	public function init() {
		parent::init();
		$this->_mobileDetect = new MobileDetect();
	}
}