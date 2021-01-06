<?php

namespace bricksasp\jobs;

/**
 * Class TeamShareJob.
 */
class TeamShareJob extends \yii\base\BaseObject implements \yii\queue\JobInterface
{
    public $order_item_id;

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
    }
}
