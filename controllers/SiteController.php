<?php

namespace app\controllers;

use app\models\LoginForm;
use app\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'register', 'resend-verification', 'verify', 'pwreset'],
                        'allow'   => false,
                        'roles'   => ['@']
                    ],
                    [
                        'allow' => true,
                    ]
                ],
            ],
            'verbs'  => [
                'class'   => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error'   => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class'           => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {

        if (!Yii::$app->user->isGuest) {
            return $this->redirect(Url::to(['data/index']));
        }


        // IDEE: facility laststate - bei offline oder error mail an user und mich und das nur einmalig wenn sich der state ändert.
        // IDEE: tabelle: facility state changes wo bei ÄNDERUNG ein eintrag mit zeit erstellt wird: histrie von ausfällen etc.

        // TODO: Bei jedem Harvesten auch die verfügbaren Facilitoes des Users testen - ist bei uns ne ID, die Vaillant nicht zurückgibt, Facility deaktivieren und ne Mail an User senden.
        // TODO: Ebenso bei neuen Facilities.  Geht das überhaupt??
        return $this->render('index');
    }

    public function actionRegister()
    {
        $model = new User(['scenario' => 'register']);
        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
            if ($model->validate() && $model->save()) {
                $model->access_token = \Yii::$app->security->generateRandomString();
                Yii::$app->mailer->compose()
                    ->setTo([$model->email => $model->first_name . ' ' . $model->last_name])
                    ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                    ->setReplyTo([Yii::$app->params['adminEmail'] => Yii::$app->params['senderName']])
                    ->setSubject(Yii::t('site', 'Your registration'))
                    ->setTextBody(Yii::t('site', "Hi!\n\nThanks for your registration at MultimaticWeb!\n\nTo verify your email and get access to your account, click here:\nhttps://multimaticweb.net/site/verify?key={key}\n\nRegards,", ['key' => $model->access_token]))
                    ->send();
                return $this->render('register_success');
            }
        }
        if (Yii::$app->params['env'] == 'dev' && Yii::$app->request->get('secret', false) != 'bro') {
            throw new HttpException(401, 'Sorry, this is development environment. Without a special secret you are not allowed to do this ;)');
        }

        return $this->render('register', ['model' => $model]);
    }

    public function actionResendVerification()
    {
        if (Yii::$app->request->isPost) {
            $u = User::findOne(['email' => Yii::$app->request->post('email', '')]);

            if ($u && $u->status == User::STATUS_NEW) {
                $u->access_token = \Yii::$app->security->generateRandomString();
                $u->save();
                Yii::$app->mailer->compose()
                    ->setTo([$u->email => $u->first_name . ' ' . $u->last_name])
                    ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                    ->setReplyTo([Yii::$app->params['adminEmail'] => Yii::$app->params['senderName']])
                    ->setSubject(Yii::t('site', 'Your registration'))
                    ->setTextBody(Yii::t('site', "Hi!\n\nThanks for your registration at MultimaticWeb!\n\nTo verify your email and get access to your account, click here:\nhttps://multimaticweb.net/site/verify?key={key}\n\nRegards,", ['key' => $u->access_token]))
                    ->send();
            }
            return $this->render('resend-verification', ['sent' => true]);
        }
        return $this->render('resend-verification');
    }

    public function actionVerify()
    {
        $user = User::findIdentityByAccessToken(Yii::$app->request->get('key', ''));

        if ($user) {
            $user->access_token = null;
            $user->status       = User::STATUS_ACTIVE;
            $user->save();
            return $this->redirect(Url::to(['site/login', 'as' => 1]));
        }

        throw new NotFoundHttpException(Yii::t('site', 'This verification is not valid anymore!'));
    }

    public function actionPwreset()
    {
        if (Yii::$app->request->isPost) {

            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $u                          = User::findIdentityByAccessToken(Yii::$app->request->get('key', false));
                $u->load(Yii::$app->request->post());
                $u->scenario = 'pwreset';
                return ActiveForm::validate($u);
            }

            if (empty(Yii::$app->request->post('email', ''))) {
                $u = User::findIdentityByAccessToken(Yii::$app->request->get('key', false));
                $u->load(Yii::$app->request->post());
                $u->scenario = 'pwreset';
                if ($u->validate()) {
                    $u->access_token = null;
                    $u->password     = \Yii::$app->security->generatePasswordHash($u->password);
                    $u->save(false);
                    return $this->redirect(Url::to(['site/login', 'as' => 2]));
                } else {
                    throw new HttpException(print_r($u->getErrors(), true));
                }
            }
            $u = User::findOne(['email' => Yii::$app->request->post('email', '')]);

            if ($u && $u->status == User::STATUS_ACTIVE) {
                $u->access_token = \Yii::$app->security->generateRandomString();
                $u->save(false);
                Yii::$app->mailer->compose()
                    ->setTo([$u->email => $u->first_name . ' ' . $u->last_name])
                    ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                    ->setReplyTo([Yii::$app->params['adminEmail'] => Yii::$app->params['senderName']])
                    ->setSubject(Yii::t('site', 'Password reset'))
                    ->setTextBody(Yii::t('site', "Seems like you forgot your password - but I can help you!\n\nClick this link ;)\nhttps://multimaticweb.net/site/pwreset?key={key}\n\nRegards,", ['key' => $u->access_token]))
                    ->send();
            }
            return $this->render('pwreset', ['sent' => true, 'user' => false]);
        }

        if (!empty(Yii::$app->request->get('key', false))) {
            $u = User::findIdentityByAccessToken(Yii::$app->request->get('key', false));
            if (!$u || $u->status != User::STATUS_ACTIVE) {
                throw new NotFoundHttpException(Yii::t('site', 'This reset-link is not valid anymore!'));
            }
        }
        return $this->render('pwreset', ['user' => isset($u) ? $u : false]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionContact($do = false)
    {
        if ($do) {
            header("Location: mailto:contact@multimaticweb.net?subject=I have a question");
            exit;
        }
        return $this->render('contact');
    }

    public function actionChangelog()
    {
        return $this->render('changelog');
    }
}
