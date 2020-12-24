<?php

namespace bricksasp\jobs;

/**
 * Class OrderDistribution.
 * 订单派发队列
 */
class OrderDistribution extends \yii\base\BaseObject implements \yii\queue\JobInterface
{
    public $order_id;

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
    }
}
