<?php

use yii\db\Migration;

/**
 * Class m200307_130008_add_default_user_tbl
 */
class m200307_130008_add_default_user_tbl extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->createTable('user', [
            'id'           => $this->primaryKey(),
            'email'        => $this->string(),
            'password'     => $this->string(),
            'auth_key'     => $this->string(),
            'access_token' => $this->string()
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200307_130008_add_default_user_tbl cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200307_130008_add_default_user_tbl cannot be reverted.\n";

        return false;
    }
    */
}
