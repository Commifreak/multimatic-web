<?php

use yii\db\Migration;

/**
 * Class m200307_132907_add_admin_debug_permission
 */
class m200307_132907_add_admin_debug_permission extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $auth               = Yii::$app->authManager;
        $admin              = $auth->createRole('admin');
        $admin->description = 'Administrator';

        $auth->add($admin);

        $viewDebugger              = $auth->createPermission('viewDebugger');
        $viewDebugger->description = 'Kann Debugger sehen';

        $auth->add($viewDebugger);

        $auth->addChild($admin, $viewDebugger);

        $auth->assign($admin, 1);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200307_132907_add_admin_debug_permission cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200307_132907_add_admin_debug_permission cannot be reverted.\n";

        return false;
    }
    */
}
