<?php

namespace app\models;

use app\components\VaillantAPI;
use yii\db\ActiveRecord;

/**
 * Class User
 * @property integer $id
 * @property string $email
 * @property string $password
 * @property string $first_name
 * @property string $last_name
 * @property string $v_username
 * @property string $v_password
 * @property integer $harvester_status
 * @property string $auth_key
 * @property string $access_token
 * @property string $status
 *
 */
class User extends ActiveRecord implements \yii\web\IdentityInterface
{

    const STATUS_NEW = 0;
    const STATUS_ACTIVE = 10;
    const STATUS_INACTIVE = 99;
    const HARVESTER_DISABLED = 0;
    const HARVESTER_ENABLED = 1;
    public $password_repeat;

    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * Finds user by username
     *
     * @param string $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::find()->where(['like', 'email', $email, false])->one();
    }

    public function rules()
    {
        return [
            ['id', 'integer'],
            [['email', 'password', 'first_name', 'last_name', 'auth_key', 'access_token', 'v_username', 'v_password'], 'string'],
            [['email', 'first_name', 'last_name', 'v_username', 'v_password'], 'required', 'except' => 'pwreset'],
            ['v_username', 'checkVaillantUsernameTaken', 'except' => 'pwreset'],
            [['v_username', 'v_password'], 'checkVaillantLogin', 'except' => 'pwreset'],
            [['password', 'password_repeat'], 'required', 'on' => ['register', 'pwreset']],
            [['password'], 'string', 'min' => 8, 'on' => ['register', 'pwreset']],
            [['password_repeat'], 'compare', 'compareAttribute' => 'password', 'message' => \Yii::t('site', 'Passwords do not match!')],
            [['email'], 'unique'],
            [['status'], 'default', 'value' => self::STATUS_NEW],
            [['harvester_status'], 'default', 'value' => self::HARVESTER_ENABLED]
        ];
    }

    public function attributeLabels()
    {
        return [
            'v_username' => \Yii::t('site', 'Vaillant Username'),
            'v_password' => \Yii::t('site', 'Vaillant Password'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return \Yii::$app->security->validatePassword($password, $this->password);
    }

    public function checkVaillantLogin($attribute, $params)
    {
        if (empty($this->v_username) || empty($this->v_password)) {
            return;
        }
        $va = new VaillantAPI($this->v_username, $this->v_password);
        if (!$va->getFacilities()) {
            $this->addError($attribute, \Yii::t('site', 'Your Vaillant login seems incorrect :('));
            \Yii::warning($va->getLastHttpCode());
        }
    }

    public function checkVaillantUsernameTaken($attribute, $params)
    {
        if (User::find()->where(['like', 'v_username', $this->v_username, false])->count() != 0) {
            $this->addError($attribute, \Yii::t('site', 'This Vaillant username is already registered!'));
        }
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->auth_key = \Yii::$app->security->generateRandomString();
                $this->password = \Yii::$app->security->generatePasswordHash($this->password);
            }
            return true;
        }
        return false;
    }

    public function getFacilities()
    {
        return $this->hasMany(Facility::class, ['uid' => 'id']);
    }
}
