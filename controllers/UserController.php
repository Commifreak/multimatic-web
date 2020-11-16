<?php

namespace app\controllers;


use app\models\User;
use yii\filters\AccessControl;
use yii\web\Controller;


class UserController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionToggleHarvesterStatus()
    {
        $user = User::findOne(\Yii::$app->user->id);

        if (\Yii::$app->request->post('status', false) == 'true') {
            $user->harvester_status = User::HARVESTER_ENABLED;
        } else {
            $user->harvester_status = User::HARVESTER_DISABLED;
        }

        $user->save(false);
    }

}

?>