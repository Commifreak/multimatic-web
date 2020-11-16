<?php

use yii\db\Migration;

/**
 * Class m200328_170156_change_timestamp_fields_to_string
 */
class m200328_170156_change_timestamp_fields_to_string extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->alterColumn('facility', 'last_sync', $this->string()->defaultValue(null));
        $this->alterColumn('data', 'time', $this->string()->defaultValue(null));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200328_170156_change_timestamp_fields_to_string cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200328_170156_change_timestamp_fields_to_string cannot be reverted.\n";

        return false;
    }
    */
}
