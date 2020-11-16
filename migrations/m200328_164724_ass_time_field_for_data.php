<?php

use yii\db\Migration;

/**
 * Class m200328_164724_ass_time_field_for_data
 */
class m200328_164724_ass_time_field_for_data extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->addColumn('data', 'time', $this->timestamp()->defaultValue(null)->append('')->after('value'));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200328_164724_ass_time_field_for_data cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200328_164724_ass_time_field_for_data cannot be reverted.\n";

        return false;
    }
    */
}
