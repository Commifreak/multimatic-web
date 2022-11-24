<?php

use yii\db\Migration;

/**
 * Class m200307_130817_add_admin_user
 */
class m200307_130817_add_admin_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->addColumn('user', 'first_name', $this->string()->after('password'));
        $this->addColumn('user', 'last_name', $this->string()->after('first_name'));

        // Ask for user
        $email    = \yii\helpers\BaseConsole::input('Please choose a email: ');
        $password = \yii\helpers\BaseConsole::input('Please chose a password: ');
        $fn       = \yii\helpers\BaseConsole::input('Your first name: ');
        $ln       = \yii\helpers\BaseConsole::input('Your last name: ');


        $this->insert('user', [
            'first_name' => $fn,
            'last_name'  => $ln,
            'email'      => $email,
            'password'   => \Yii::$app->security->generatePasswordHash($password),
            'auth_key'   => \Yii::$app->security->generateRandomString(),
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->truncateTable('user');

        $this->dropColumn('user', 'first_name');
        $this->dropColumn('user', 'last_name');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200307_130817_add_admin_user cannot be reverted.\n";

        return false;
    }
    */
}
