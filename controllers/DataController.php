<?php

namespace app\controllers;


use app\models\Chart;
use app\models\Facility;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

class DataController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => false,
                        'roles' => ['?']
                    ],
                    [
                        'allow'         => false,
                        'actions'       => ['chart', 'create-chart'],
                        'matchCallback' => function () {
                            // Check, if fid is in the list of users Facilities
                            if (!in_array(\Yii::$app->request->get('fid', ''), ArrayHelper::getColumn(\Yii::$app->user->identity->facilities, 'id'))) {
                                return true;
                            }
                            return false;
                        },
                        'denyCallback'  => function () {
                            throw new ForbiddenHttpException('Sorry, you dont own this facility!');
                        }
                    ],
                    [
                        'allow'         => false,
                        'actions'       => ['edit-chart', 'delete-chart', 'get-chart'],
                        'matchCallback' => function () {
                            // Check, if fid is in the list of users Facilities
                            $chart = Chart::findOne(['id' => \Yii::$app->request->get('id', '')]);
                            if (!$chart || !in_array($chart->fid, ArrayHelper::getColumn(\Yii::$app->user->identity->facilities, 'id'))) {
                                return true;
                            }
                            return false;
                        },
                        'denyCallback'  => function () {
                            throw new ForbiddenHttpException('Sorry, you dont own this chart!');
                        }
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }


    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionChart($fid)
    {


        return $this->render('chart', ['fid' => $fid]);
    }

    public function actionCreateChart($fid)
    {
        $facility = Facility::findOne(['id' => $fid]);

        $model      = new Chart();
        $model->fid = $facility->id;

        if (\Yii::$app->request->isPost) {
            $model->load(\Yii::$app->request->post());
            if (\Yii::$app->request->isAjax) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }

            if ($model->save()) {
                return $this->redirect(Url::to(['/data/chart', 'fid' => $fid]));
            } else {
                return print_r($model->getErrors(), true);
            }

        }

        return $this->renderAjax('chart-modal', ['facility' => $facility, 'model' => $model]);
    }


    public function actionEditChart($id)
    {

        $model = Chart::findOne(['id' => $id]);

        if (!$model) {
            throw new NotFoundHttpException('Chart not found!');
        }

        if (\Yii::$app->request->isPost) {
            $model->load(\Yii::$app->request->post());
            if (\Yii::$app->request->isAjax) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }

            if ($model->save()) {
                return $this->redirect(Url::to(['/data/chart', 'fid' => $model->fid]));
            } else {
                return print_r($model->getErrors(), true);
            }

        }

        return $this->renderAjax('chart-modal', ['facility' => $model->facility, 'model' => $model]);
    }


    public function actionDeleteChart($id)
    {

        $model = Chart::findOne(['id' => $id]);

        if (!$model) {
            throw new NotFoundHttpException('Chart not found!');
        }

        if ($model->delete()) {
            return true;
        } else {
            throw new BadRequestHttpException('Something went wrong!');
        }
    }

    public function actionGetChart($id)
    {
        $model = Chart::findOne(['id' => $id]);

        if (!$model) {
            throw new NotFoundHttpException('Chart not found!');
        }


        return $this->renderAjax('get-chart', ['chart' => $model, 'move' => \Yii::$app->request->get('move', false)]);
    }
}