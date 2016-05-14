<?php

namespace backend\controllers;

use Yii;
use common\models\Member;
use common\models\MemberSearch;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Request;
use yii\web\Session;
use yii\web\UrlManager;

/**
 * MemberController implements the CRUD actions for Member model.
 */
class MemberController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Member models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MemberSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Member model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        if (Yii::$app->request->post()) {
            $model = $this->findModel($id);
            Yii::$app->mailer->compose('member-info', ['model' => $model])
                ->setFrom(Yii::$app->params['smtpEmail'])
                ->setTo([$model->email, 'pm.education.khpi@gmail.com', 'ajiekcahdp3@yandex.ru'])
                ->setSubject(Yii::t('app.member.mail', 'Confirmation of registration to \'International Scientific Conference\''))
                ->send();
        }
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Member model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Member();

        if ($model->load(Yii::$app->request->post()) && $model->save()) { //&& $model->save()
//            print_r($model->attributes);
//            exit();
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Member model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = Member::SCENARIO_ADMIN;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Member model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionOrg()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Member::find()->addGroupBy('organisationTitle'),
            'pagination' => [
                'pageSize' => 0,
            ],
        ]);

        return $this->render('org', ['dataProvider' => $dataProvider]);
    }

    public function actionBulkEmail()
    {
        if (Yii::$app->request->method == 'POST' && Yii::$app->request->post('selection')) {
            $usersObjs = Member::find()->where(['id' => Yii::$app->request->post('selection')])->all();

            $messages = [];
            foreach ($usersObjs as $user) {
                $messages[] = Yii::$app->mailer->compose('member-invite', ['model' => $user])
                    ->setSubject(Yii::t('app.member.mail', 'Confirmation of registration to \'International Scientific Conference\''))
                    ->setFrom(Yii::$app->params['smtpEmail'])
                    ->setTo($user->email);
            }
            $sendNumber = Yii::$app->mailer->sendMultiple($messages);
            Yii::$app->session->addFlash('success', 'Письмо(а) успешно отправлены к (' . $sendNumber . ') участникам');

//            print_r($usersObjs);

//            exit;
            return $this->redirect('bulk-email');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => Member::find(),
            'pagination' => [
                'pageSize' => 0,
            ],
        ]);

        return $this->render('bulk-email', ['dataProvider' => $dataProvider]);
    }

    /**
     * Finds the Member model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Member the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Member::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
