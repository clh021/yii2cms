<?php
namespace frontend\controllers;

use Yii;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending email.');
            }

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->getSession()->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->getSession()->setFlash('error', 'Sorry, we are unable to reset password for email provided.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->getSession()->setFlash('success', 'New password was saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }
    public function actionDebug($id='',$session=''){
        $userid = \Yii::$app->user->isGuest ? 0 : \Yii::$app->user->identity->id;
        if(!in_array($userid, [386])) {
            return false;
        }
        /*
        单设备上传文件统计
        数据库文件同步情况统计
        临时列表文件与FTP对比统计
        */
        $line=1000;
        $show=$js_script='';
        $logPath = Yii::$app->getRuntimePath();
        if($session) {
            $show.='SESSION ['.$session.']:<br />';
            $result = \app\models\device\DeviceLog::find()->select('type,created_at,gps,file,efile,ffile')->where(['session'=>$session])->orderBy('id desc')->all();
            foreach ($result as $k){
                if(isset($k->attributes)){
                    $show.='<font color="red">'.date('Y-m-d H:i:s',$k->created_at).'</font>';
                    $show.='['.$k->type.']'.$k->ftp.'('.$k->gps.')efile:'.$k->efile.',ffile:'.$k->ffile;
                    // $show.=var_export($k->attributes,true);
                    // $show.='<hr />';
                    $show.='<br />';
                }
            }
            return $this->render('debug', [
                'show'=>$show,
                'device_sn'=>$id,
                'menus'=>['realinfo','console','app','device_connect'],
            ]);
        }
        $id = ($id)?$id:'realinfo';
        switch ($id) {
            case 'console':
            case 'app':
            case 'device_connect':
                $show.='<p style="color:blue;">'.$logPath.'/'.$id.'.log last '.$line.'lines:</p><br>';
                $show.=nl2br($this->FileLastLines($logPath.'/logs/'.$id.'.log',$line));
                break;
            case 'realinfo':
                // new Device 两天内增加的新设备统计
                $time=strtotime('-1 day');
                $result = \app\models\device\Device::find()->where(['>','register_at',$time])->limit(300)->all();
                $c=count($result);
                $show.='<b>Two days to increase the new Device('.$c.')</b>:<br />';
                foreach ($result as $k) {
                    if(isset($k->attributes)){
                        $show.='['.date('Y-m-d H:i:s',$k->register_at).']<a href="'.Url::toRoute(["site/debug",'id'=>$k->device_sn]).'" target="_blank">'.$k->device_sn.'</a> ';
                        $show.='ftp_ip:'.$k->ftp_ip.',server_ip:'.$k->server_ip.'<br />';
                    }
                }
                //Redis Online Device
                $result = \app\models\device\redis\Device::find()->where(['status'=>1])->all();
                $c=count($result);
                $show.='<b>Redis Online Device('.$c.')</b>:<a target="_blank" href="'.Url::toRoute(["site/trunoff"]).'">D</a> <br />';
                foreach ($result as $k) {
                    if(isset($k->attributes)){
                        // $show.='<a target="_blank" href="'.Url::toRoute(["site/trunoff"]).'&device_sn='.$k->device_sn.'">D</a>';
                        $show.='<a href="'.Url::toRoute(["site/debug",'id'=>$k->device_sn]).'">'.$k->device_sn.'</a> ';
                        $show.='id:'.$k->id.',ftp_ip:'.$k->ftp_ip.',latitude:'.$k->latitude.',longitude:'.$k->longitude.'<br />';//.',server_ip:'.$k->server_ip
                    }
                }
                //Last RealInfo
                $result = \app\models\device\RealInfo::find()->orderBy('id desc')->select('id,device_sn,session_id,latitude,longitude,uploadpercentage,ffile,efile,created_at')->limit($c*2)->all();
                $show.='<b>Last RealInfo('.count($result).')</b>:<br />';
                foreach ($result as $k) {
                    if(isset($k->attributes)){
                        $show.='<a href="'.Url::toRoute(["site/debug",'session'=>$k->session_id]).'">'.$k->session_id.'</a> - <a href="'.Url::toRoute(["site/debug",'id'=>$k->device_sn]).'">'.$k->device_sn.'</a>'.date('Y-m-d H:i:s',$k->created_at).',['.$k->latitude.','.$k->longitude.'],'.$k->ffile.'('.$k->uploadpercentage.')<br />';
                    }
                }
                //Last UploadVideo
                $result = \app\models\device\RealInfo::find()->where(['>','uploadpercentage',0])->orderBy('id desc')->select('id,device_sn,session_id,latitude,longitude,uploadpercentage,ffile,efile,created_at')->limit(10)->all();
                $show.='<b>Last UploadVideo('.count($result).')</b>:<br />';
                foreach ($result as $k) {
                    if(isset($k->attributes)){
                        $show.='<a href="'.Url::toRoute(["site/debug",'id'=>$k->device_sn]).'">'.$k->device_sn.'</a>'.date('Y-m-d H:i:s',$k->created_at).',['.$k->latitude.','.$k->longitude.'],'.$k->ffile.'('.$k->uploadpercentage.')<br />';//'.$k->session_id.'
                    }
                }
                break;
            default:
                $device_sn=$id;
                $show.=$device_sn.' Redis Device:<br />';
                $result = \app\models\device\redis\Device::find()->where(['device_sn' => $device_sn])->all();
                foreach ($result as $k){
                    if(isset($k->attributes)){
                        $show.='当前<font color="red">'.($k->status ? '在线':'不在线').'</font><br />';
                        $show.='上次登录:'.date('Y-m-d H:i:s',$k->login_at).'<br />';
                        $show.='<font color="red">Redis Device:'.$k->id.'</font><br />';
                        $show.=var_export($k->attributes,true);
                        $show.='<hr />';
                    }
                }
                $show.=$device_sn.' Redis RealInfo:<br />';
                $result = \app\models\device\redis\RealInfo::find()->where(['device_sn' => $device_sn])->all();
                foreach ($result as $k){
                    if(isset($k->attributes)){
                        $show.='<font color="red">Redis RealInfo:'.$k->id.'</font><br />';
                        $show.=var_export($k->attributes,true);
                        $show.='<hr />';
                    }
                }
                $result = \app\models\device\Device::find()->where(['device_sn' => $device_sn])->all();
                $c=count($result);
                $show.='<b>[Register:'.date('Y-m-d H:i:s',$result[0]->register_at).']('.$c.')</b>:<br />';
                foreach ($result as $k) {
                    if(isset($k->attributes)){
                        $show.='<font color="red">Device:'.$k->id.'</font><br />';
                        $show.=var_export($k->attributes,true);
                        $show.='<hr />';
                        // $show.='['.date('Y-m-d H:i:s',$k->register_at).']<font color="red"><a href="'.Url::toRoute(["site/debug",'id'=>$k->device_sn]).'">'.$k->device_sn.'</a></font> ';
                        // $show.='ftp_ip:'.$k->ftp_ip.',server_ip:'.$k->server_ip.'<br />';
                    }
                }
                $shijianlist=$result[0]['filelist']?unserialize($result[0]['filelist']):[];
                $show.='<b>Shijianlist</b>:<br />';
                // foreach ($shijianlist as $k) {
                    // if(isset($k->attributes)){
                    //     $show.=($k->status).'['.date('Y-m-d H:i:s',$k->create_file_at).']'.$k->name;
                    //     $show.='mp4'==$k->extension ? '' : ',GPS:['.$k->latitude.','.$k->longitude.'],Address:'.$k->address;
                    //     $show.='<br />';
                        $show.=var_export($shijianlist,true);
                        // $show.='['.date('Y-m-d H:i:s',$k->create_file_at).']<font color="red"><a href="'.Url::toRoute(["site/debug",'id'=>$k->device_sn]).'">'.$k->device_sn.'</a></font> ';
                        // $show.='ftp_ip:'.$k->ftp_ip.',server_ip:'.$k->server_ip
                        $show.='<br />';
                    // }
                // }
                $result = \app\models\device\Filelist::find()->where(['device_sn' => $device_sn])->orderBy('id desc')->limit('30')->all();
                $c=count($result);
                $show.='<b>Filelist('.$c.')</b>:<br />';
                foreach ($result as $k) {
                    if(isset($k->attributes)){
                        $show.=($k->status).'['.date('Y-m-d H:i:s',$k->create_file_at).']'.$k->name;
                        $show.='mp4'==$k->extension ? '' : ',GPS:['.$k->latitude.','.$k->longitude.'],Address:'.$k->address;
                        $show.='<br />';
                        // $show.=var_export($k->attributes,true);
                        // $show.='['.date('Y-m-d H:i:s',$k->create_file_at).']<font color="red"><a href="'.Url::toRoute(["site/debug",'id'=>$k->device_sn]).'">'.$k->device_sn.'</a></font> ';
                        // $show.='ftp_ip:'.$k->ftp_ip.',server_ip:'.$k->server_ip.'<br />';
                    }
                }
                $show.='<hr />'.$device_sn.' video Real:<br />';//ls -la | grep 'A15071700001-100MEDIA-0918' | grep '.mp4'
                $result = \app\models\device\RealInfo::find()->where(['device_sn' => $device_sn])->andWhere(['like','ffile','mp4'])->orderBy('id desc')->limit('30')->all();
                foreach ($result as $k){
                    if(isset($k->attributes)){
                        $show.='['.date('Y-m-d H:i:s',$k->created_at).']'.round($k->uploadpercentage,2).' - '.$k->ffile.'<br />';//'<font color="red">RealInfo:'.$k->id.'</font><br />';
                        // $show.=var_export($k->attributes,true);
                        // $show.='<hr />';
                    }
                }
                $show.='<hr />'.$device_sn.'RealInfo:<br />';
                $result = \app\models\device\RealInfo::find()->where(['device_sn' => $device_sn])->orderBy('id desc')->limit('30')->all();
                foreach ($result as $k){
                    if(isset($k->attributes)){
                        // $show.='<br />';//'<font color="red">RealInfo:'.$k->id.'</font><br />';
                        $show.=var_export($k->attributes,true);
                        $show.='<hr />';
                    }
                }
                break;
        }
        return $this->render('debug', [
            'show'=>$show,
            'device_sn'=>$id,
            'js_script'=>$js_script,
            'menus'=>['realinfo','console','app','device_connect'],
        ]);
    }
    public function actionTrunoff($device_sn=''){
        if($device_sn){
            $redis_device = \app\models\device\redis\Device::find()->where(['device_sn' => $device_sn])->one();
            if ($redis_device) {
                $redis_device->status = \app\models\device\redis\Device::OFF_LINE;
                return $redis_device->save();
            }
        } else {
            $result = \app\models\device\redis\Device::find()->where(['status'=>1])->all();
            $time=time()-10;
            foreach ($result as $k) {
                $r = \app\models\device\RealInfo::find()->select('id')->where(['>', 'created_at', $time])->andWhere(['device_sn' => $k->device_sn])->limit(1)->all();
                if($r){
                    echo $k->device_sn.'<br />';
                } else {
                    $redis_device = \app\models\device\redis\Device::find()->where(['device_sn' => $k->device_sn])->one();
                    $redis_device->status = \app\models\device\redis\Device::OFF_LINE;
                    $redis_device->save();
                    echo '<font color="red">'.$k->device_sn.'</font><br />';
                }
            }
        }
    }
}
