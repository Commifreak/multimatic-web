<?php

use yii\db\Migration;

/**
 * Class m200311_184604_add_vaillant_user_and_pw
 */
class m200311_184604_add_vaillant_user_and_pw extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->addColumn('user', 'v_username', $this->string()->after('last_name')->comment('Vaillant user'));
        $this->addColumn('user', 'v_password', $this->string()->after('v_username')->comment('Vaillant pass'));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user', 'v_username');
        $this->dropColumn('user', 'v_password');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200311_184604_add_vaillant_user_and_pw cannot be reverted.\n";

        return false;
    }
    */
}
