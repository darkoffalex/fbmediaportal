<?php

use yii\db\Migration;

/**
 * Handles adding reason to table `stock_recommendation`.
 */
class m170124_170355_add_reason_column_to_stock_recommendation_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('stock_recommendation', 'reason_type_id', $this->integer());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('stock_recommendation', 'reason_type_id');
    }
}
