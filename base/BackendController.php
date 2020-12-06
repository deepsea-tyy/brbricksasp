<?php

namespace bricksasp\base;

class BackendController extends \bricksasp\base\BaseController
{
	public function behaviors() {
		return [
			'adminauth' => [
				'class' => CompositeAuth::className(),
			],
		];
	}
}
