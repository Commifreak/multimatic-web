<?php

use yii\db\Migration;

/**
 * Class m200328_122425_add_more_facility_meta_cols
 */
class m200328_122425_add_more_facility_meta_cols extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->addColumn('facility', 'box_status', $this->string()->after('firmware'));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200328_122425_add_more_facility_meta_cols cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200328_122425_add_more_facility_meta_cols cannot be reverted.\n";

        return false;
    }
    */
}
