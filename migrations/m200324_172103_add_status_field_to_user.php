<?php

use app\models\User;
use yii\db\Migration;

/**
 * Class m200324_172103_add_status_field_to_user
 */
class m200324_172103_add_status_field_to_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->addColumn('user', 'status', $this->integer(2)->defaultValue(User::STATUS_NEW));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200324_172103_add_status_field_to_user cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200324_172103_add_status_field_to_user cannot be reverted.\n";

        return false;
    }
    */
}
