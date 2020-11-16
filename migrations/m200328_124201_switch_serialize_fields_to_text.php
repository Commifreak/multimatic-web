<?php

use yii\db\Migration;

/**
 * Class m200328_124201_switch_serialize_fields_to_text
 */
class m200328_124201_switch_serialize_fields_to_text extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->alterColumn('facility', 'network_info', $this->text());
        $this->alterColumn('facility', 'box_status', $this->text());

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200328_124201_switch_serialize_fields_to_text cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200328_124201_switch_serialize_fields_to_text cannot be reverted.\n";

        return false;
    }
    */
}
