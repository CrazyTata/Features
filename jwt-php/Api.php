<?php

namespace app\index\controller;

use Firebase\JWT\JWT;
use think\Controller;
use think\facade\Request;

class Api extends Controller
{
    /**
     * 状态码
     */
    const HTTP_STATUS_SUCCESS = 0;    // 操作成功
    const HTTP_STATUS_ERROR = 400;    // 操作失败
    const HTTP_STATUS_NO_PERMISSION = 403;    // 没有权限
    const HTTP_STATUS_NOT_FOUND = 404;    // 404找不到
    const HTTP_STATUS_FAIL = 500;    // 发生错误
    const HTTP_STATUS_NOT_LOGGED = 100;    // 未登录

    /**
     * 生成 Token
     *
     * @param  array $payload 载荷
     * @return string
     */
    public function createToken(array $payload = ['name'=>'zhangsan'])
    {
        try {
            $config = config('token');

            $key = $config['application_signature_secret'];
            $access_token_expired = $config['access_token_expired'];

            $payload = array_merge([
                'iss' => $config['issuer'],
                "aud" => $config['issuer'],
                'iat' => $_SERVER['REQUEST_TIME'],
                'nbf' => $_SERVER['REQUEST_TIME'],
                'exp' => $_SERVER['REQUEST_TIME'] + $access_token_expired
            ], $payload);

            return JWT::encode($payload, $key);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function checkToken(Request $request){
    	
    	return json_encode($this->authToken());
    }

    /**
     * 验证 Token
     * @param null $access_token
     * @return bool|object
     */
    protected function authToken($access_token=null)
    {
    	$access_token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJTWSIsImF1ZCI6IlNZIiwiaWF0IjoxNTczNDI3NjMzLCJuYmYiOjE1NzM0Mjc2MzMsImV4cCI6MTU3NDAzMjQzMywibmFtZSI6InpoYW5nc2FuIn0.bYfVra1zQcDjCdwzuN3xPFUZrL1SjwN2De2hRixZPvQ';
        try {
            $key = config('token')['application_signature_secret'];

            if (
                ($jwt = $access_token ? $access_token :($_SERVER['HTTP_AUTHORIZATION'] ? $_SERVER['HTTP_AUTHORIZATION'] : FALSE))
                && ($payload = JWT::decode($jwt, $key, array('HS256')))
            ) {
                return $payload;
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }


    /**
     * 操作成功
     *
     * @param  string $msg 响应信息
     * @param  mixed $info 响应数据
     */
    protected function successful($msg = '', $info = null)
    {
        $this->responseJSON(self::HTTP_STATUS_SUCCESS, $msg, $info);
    }

    /**
     * 操作失败
     *
     * @param  string $msg 响应信息
     * @param  mixed $info 响应数据
     */
    protected function failed($msg = '', $info = null)
    {
        $this->responseJSON(self::HTTP_STATUS_ERROR, $msg, $info);
    }

    /**
     * 未找到数据
     *
     * @param  string $msg 响应信息
     * @param  mixed $info 响应数据
     */
    protected function notFound($msg = '未找到相关数据', $info = null)
    {
        $this->responseJSON(self::HTTP_STATUS_NOT_FOUND, $msg, $info);
    }

    /**
     * 未登录
     *
     * @param  string $msg 响应信息
     * @param  mixed $info 响应数据
     */
    protected function notLogged($msg = '未登录', $info = null)
    {
        $this->responseJSON(self::HTTP_STATUS_NOT_LOGGED, $msg, $info);
    }


    /**
     * 没有权限
     *
     * @param  string $msg 响应信息
     * @param  mixed $info 响应数据
     */
    protected function noAccess($msg = '没有权限', $info = null)
    {
        $this->responseJSON(self::HTTP_STATUS_NO_PERMISSION, $msg, $info);
    }

    /**
     * 发生错误
     *
     * @param  string $msg 响应信息
     * @param  mixed $info 响应数据
     */
    protected function fail($msg = '发生错误', $info = null)
    {
        $this->responseJSON(self::HTTP_STATUS_FAIL, $msg, $info);
    }


    /**
     * 输出 JSON 响应
     *
     * @param  integer $code 响应状态
     * @param  string $msg 响应信息
     * @param  mixed $info 响应数据
     */
    protected function responseJSON($code, $msg = '', $info = NULL)
    {
        $response = [
            'code' => $code,
            'msg' => $msg
        ];

        $info !== NULL && $response['info'] = $info;

        header('Content-type:application/json');
        exit(json_encode($response, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 获取 GET 参数
     *
     * @param  string $name 参数名
     * @param  mixed $default 默认值
     * @return mixed
     */
    protected function get($name = NULL, $default = NULL)
    {
        if (!$name)
            return $_GET;

        return $_GET[$name] ? $_GET[$name] : $default;
    }

    /**
     * 获取 POST 参数
     *
     * @param  string $name 参数名
     * @param  mixed $default 默认值
     * @return mixed
     */
    protected function post($name = NULL, $default = NULL)
    {
        if (!$name)
            return $_POST;

        return $_POST[$name] ? $_POST[$name] : $default;
    }

    /**
     * 是否GET请求
     *
     * @return boolean
     */
    protected function isGet()
    {
        return Request::isGet();
    }

    /**
     * 是否POST请求
     *
     * @return boolean
     */
    protected function isPost()
    {
        return Request::isPost();
    }
}
