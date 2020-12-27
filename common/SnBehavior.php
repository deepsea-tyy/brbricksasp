<?php
namespace bricksasp\common;

use Yii;
use bricksasp\base\Tools;
use yii\db\BaseActiveRecord;
use yii\base\InvalidCallException;
use yii\behaviors\AttributeBehavior;

/**
 * 默认编号字段
 */
class SnBehavior extends AttributeBehavior
{
    const SN_ORDER        = 1; //订单编号
    const SN_PAY          = 2; //支付单编号
    const SN_GOODS        = 3; //商品编号
    const SN_PRODUCT      = 4; //单品编号
    const SN_AFTER_SALE   = 5; //售后单编号
    const SN_REFUNDS      = 6; //退款单编号
    const SN_RETURN_GOODS = 7; //退货单编号
    const SN_TRACKING_NO  = 8; //发货单编号
    const SN_PICK_GOODS   = 9; //提货单号
    const SN_FILE         = 10;//文件编号
    const SN_PRODUCT_BARCODE = 11;//单品条码
    /**
     * @var string the attribute that will receive timestamp value
     * Set this property to false if you do not want to record the creation id.
     */
    public $attribute = 'sn';

    /**
     * @inheritdoc
     *
     * In case, when the value is `null`, the result of the PHP function [time()](http://php.net/manual/en/function.time.php)
     * will be used as value.
     */
    public $value;
    public $type;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (empty($this->attributes)) {
            $this->attributes = [
                BaseActiveRecord::EVENT_BEFORE_INSERT => [$this->attribute],
            ];
        }
    }

    /**
     * @inheritdoc
     *
     * In case, when the [[value]] is `null`, the result of the PHP function [time()](http://php.net/manual/en/function.time.php)
     * will be used as value.
     */
    protected function getValue($event)
    {
        if ($this->type === null) {
            throw new InvalidCallException('未设置编号类型');
        }
        if (empty($this->value)) $this->value = Tools::get_sn($this->type);

        return parent::getValue($event);
    }
}
