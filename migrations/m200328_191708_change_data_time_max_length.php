<?php

use yii\db\Migration;

/**
 * Class m200328_191708_change_data_time_max_length
 */
class m200328_191708_change_data_time_max_length extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->alterColumn('data', 'time', $this->string(10));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200328_191708_change_data_time_max_length cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200328_191708_change_data_time_max_length cannot be reverted.\n";

        return false;
    }
    */
}
