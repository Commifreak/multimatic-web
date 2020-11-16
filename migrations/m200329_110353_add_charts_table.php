<?php

use yii\db\Migration;

/**
 * Class m200329_110353_add_charts_table
 */
class m200329_110353_add_charts_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->createTable('chart', [
            'id'        => $this->primaryKey(),
            'fid'       => $this->integer(),
            'dataTypes' => $this->text(),
            'view'      => $this->string()
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200329_110353_add_charts_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200329_110353_add_charts_table cannot be reverted.\n";

        return false;
    }
    */
}
