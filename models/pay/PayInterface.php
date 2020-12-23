<?php
namespace bricksasp\models\pay;

use Yii;

interface PayInterface
{
    public function pay();
    public function refund();
    public function query();
}
