<?php

use yii\db\Migration;

/**
 * Class m200329_165412_add_colors_to_charts
 */
class m200329_165412_add_colors_to_charts extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->addColumn('chart', 'colors', $this->text()->after('dataTypes'));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200329_165412_add_colors_to_charts cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200329_165412_add_colors_to_charts cannot be reverted.\n";

        return false;
    }
    */
}
