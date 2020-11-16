<?php

use yii\db\Migration;

/**
 * Class m200327_184223_add_more_facility_cols
 */
class m200327_184223_add_more_facility_cols extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->addColumn('facility', 'name', $this->string()->after('fid'));
        $this->addColumn('facility', 'network_info', $this->string()->after('name'));
        $this->addColumn('facility', 'firmware', $this->string()->after('network_info'));
        $this->addColumn('facility', 'last_sync', $this->timestamp()->after('firmware'));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200327_184223_add_more_facility_cols cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200327_184223_add_more_facility_cols cannot be reverted.\n";

        return false;
    }
    */
}
