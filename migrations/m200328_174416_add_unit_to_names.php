<?php

use yii\db\Migration;

/**
 * Class m200328_174416_add_unit_to_names
 */
class m200328_174416_add_unit_to_names extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->addColumn('name', 'unit', $this->string()->after('nice_name')->defaultValue(null));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200328_174416_add_unit_to_names cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200328_174416_add_unit_to_names cannot be reverted.\n";

        return false;
    }
    */
}
