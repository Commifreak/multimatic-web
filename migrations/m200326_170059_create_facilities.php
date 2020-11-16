<?php

use yii\db\Migration;

/**
 * Class m200326_170059_create_facilities
 */
class m200326_170059_create_facilities extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->addColumn('user', 'harvester_status', $this->integer()->after('v_password')->defaultValue(1));

        $this->createTable('facility', [
            'id'     => $this->primaryKey(),
            'uid'    => $this->integer(),
            'fid'    => $this->string(),
            'status' => $this->integer()
        ]);

        $this->createIndex('idx_fac_uid', 'facility', ['uid']);

        $this->createTable('data', [
            'id'    => $this->primaryKey(),
            'fid'   => $this->integer(),
            'type'  => $this->string(),
            'value' => $this->string()
        ]);

        $this->createIndex('idx_data_fid', 'data', ['fid']);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200326_170059_create_facilities cannot be reverted.\n";

        return false;
    }
    */
}
