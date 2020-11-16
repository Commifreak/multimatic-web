<?php

use yii\db\Migration;

/**
 * Class m200328_140551_add_naming_table
 */
class m200328_140551_add_naming_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->createTable('name', [
            'id'        => $this->primaryKey(),
            'fid'       => $this->string(),
            'name'      => $this->string(),
            'nice_name' => $this->string()
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200328_140551_add_naming_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200328_140551_add_naming_table cannot be reverted.\n";

        return false;
    }
    */
}
