<?php

namespace bricksasp\base;

class FrontendController extends \bricksasp\base\BaseController
{
	public function behaviors() {
		return [
			'adminauth' => [
				'class' => CompositeAuth::className(),
			],
		];
	}
}
