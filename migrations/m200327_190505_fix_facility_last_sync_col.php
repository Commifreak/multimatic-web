<?php

use yii\db\Migration;

/**
 * Class m200327_190505_fix_facility_last_sync_col
 */
class m200327_190505_fix_facility_last_sync_col extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->alterColumn('facility', 'last_sync', $this->timestamp()->defaultValue(null)->append(''));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200327_190505_fix_facility_last_sync_col cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200327_190505_fix_facility_last_sync_col cannot be reverted.\n";

        return false;
    }
    */
}
