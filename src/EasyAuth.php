<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/10
 * Time: 22:01
 */

namespace easy\auth;


use easy\auth\sdk\QqSDK;
use easy\auth\sdk\WeixinSDK;

abstract class EasyAuth
{
    /**
     * 申请应用时分配的app_key
     * @var string
     */
    protected $AppKey = '';

    /**
     * 申请应用时分配的 app_secret
     * @var string
     */
    protected $AppSecret = '';

    /**
     * 授权类型 response_type 目前只能为code
     * @var string
     */
    protected $ResponseType = 'code';

    /**
     * grant_type 目前只能为 authorization_code
     * @var string
     */
    protected $GrantType = 'authorization_code';

    /**
     * 回调页面URL  可以通过配置文件配置
     * @var string
     */
    protected $Callback = '';

    /**
     * 获取request_code的额外参数 URL查询字符串格式
     * @var srting
     */
    protected $Authorize = '';

    /**
     * 获取request_code请求的URL
     * @var string
     */
    protected $GetRequestCodeURL = '';

    /**
     * 获取access_token请求的URL
     * @var string
     */
    protected $GetAccessTokenURL = '';
    /**
     * API根路径
     * @var string
     */
    protected $ApiBase = '';
    /**
     * 授权后获取到的TOKEN信息
     * @var array
     */
    protected $Token = null;
    /**
     * 调用接口类型
     * @var string
     */
    private $Type = '';

    public static function getInstance($type, $token = null)
    {

        switch ($type)
        {
            case 'qq': return new QqSDK();break;
            case 'weixin':return new WeixinSDK();break;
            default:
                throw new \Exception('不可用的登录类型',400);
        }


    }

    public function setAppKey($value)
    {
        $this->AppKey = $value;
    }

    public function setAppSecret($value)
    {
        $this->AppSecret = $value;
    }

    public function setCallback($value)
    {
        $this->Callback = $value;
    }
    /**
     * 请求code
     */
    public function getRequestCodeURL(){
        //Oauth 标准参数
        $params = array(
            'client_id'     => $this->AppKey,
            'redirect_uri'  => $this->Callback,
            'response_type' => $this->ResponseType,
        );

        //获取额外参数
        if($this->Authorize){
            parse_str($this->Authorize, $_param);
            if(is_array($_param)){
                $params = array_merge($params, $_param);
            } else {
                throw new \Exception('AUTHORIZE配置不正确！',400);
            }
        }
        return $this->GetRequestCodeURL . '?' . http_build_query($params);
    }
    /**
     * 发送HTTP请求方法，目前只支持CURL发送请求
     * @param  string $url    请求URL
     * @param  array  $params 请求参数
     * @param  string $method 请求方法GET/POST
     * @return array  $data   响应数据
     */
    protected function http($url, $params, $method = 'GET', $header = array(), $multi = false){
        $opts = array(
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => $header
        );

        /* 根据请求类型设置特定参数 */
        switch(strtoupper($method)){
            case 'GET':
                $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
                break;
            case 'POST':
                //判断是否传输文件
                $params = $multi ? $params : http_build_query($params);
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_POST] = 1;
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
            default:
                throw new \Exception('不支持的请求方式！');
        }

        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data  = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if($error) throw new \Exception('请求发生错误：' . $error);
        return  $data;
    }

    /**
     * 合并默认参数和额外参数
     * @param array $params  默认参数
     * @param array/string $param 额外参数
     * @return array:
     */
    protected function param($params, $param){
        if(is_string($param))
            parse_str($param, $param);
        return array_merge($params, $param);
    }

    /**
     * 获取指定API请求的URL
     * @param  string $api API名称
     * @param  string $fix api后缀
     * @return string      请求的完整URL
     */
    protected function url($api, $fix = ''){
        return $this->ApiBase . $api . $fix;
    }

}