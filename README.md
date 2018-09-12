##安装

```

composer require yii2-login dev-master

或者在composer.json中加入

 "require": {

        "yii2-login": "dev-master"
}

```
更新依赖 ``` composer update ```

##使用说明

##DEMO

```

public function actionIndex()
    {
        /*$auth = EasyAuth::getInstance('qq');
        $auth->setAppKey('*');
        $auth->setAppSecret('*');
        $auth->setCallback('http:/域名/login/callback?type=qq');
        \Yii::$app->response->redirect($auth->getRequestCodeURL());*/
        $auth = EasyAuth::getInstance('weixin');
        $auth->setAppKey('*');
        $auth->setAppSecret('*');
        $auth->setCallback('http:/域名/login/callback?type=wx');
        \Yii::$app->response->redirect($auth->getRequestCodeURL());
    }
    //回调方法
    public function actionCallback()
    {

        $code  =  \Yii::$app->request->get('code');
        $state =  \Yii::$app->request->get('state');
        $type = \Yii::$app->request->get('type');
        if($type == 'qq'){

            $auth = EasyAuth::getInstance('qq');
            $auth->setAppKey('*');
            $auth->setAppSecret('*');
            $auth->getAccessToken($code);

            $result = $auth->getUserInfo('oauth2.0/me');

        }else if($type == 'wx'){

            $auth = EasyAuth::getInstance('weixin');
            $auth->setAppKey('*');
            $auth->setAppSecret('*');
            $auth->getAccessToken($code);

            $result = $auth->getUserInfo('sns/userinfo');

        }

    }

```

