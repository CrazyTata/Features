<?php

namespace app\index\controller;


use think\facade\Config;
use think\Request;
use think\Validate;
use OSS\Core\OssException;
use OSS\OssClient as OssClientAlias;
use think\Image as ImageAlias;

class UploadController
{



    /**
     * @note 文件上传  1 上传图片
     * @author: tata
     * @since: 2019/11/7 17:27
     */
    public function uploadFile()
    {
        $file = request()->file('file');
        $type = request()->type;
        if(empty($file)){
            $res['code'] = 1;
            $res['msg'] = '文件和文件类型必传';
            json($res)->send();exit;
        }
        $data =array();
        if($type==2){
            $data = $this->ossUploadVideoFile($file);
        }else{
            $data = $this->ossUploadFile($file);
        }


        if (isset($data['imgurl'])&&!empty($data['imgurl'])) {
            $res['code'] = 0;
            $res['msg'] = "上传成功";
            $res['data'] = $data['imgurl'];
            json($res)->send();exit;
        }elseif (isset($data['videourl'])&&!empty($data['videourl'])){
            $res['code'] = 0;
            $res['msg'] = "上传成功";
            $res['data'] = $data['videourl'];
            json($res)->send();exit;
        }else {
            $res['code'] = 1;
            $res['msg'] = "上传失败,请重新上传";
            $res['data'] = $data;
            json($res)->send();exit;
        }


    }

    /**
     * @param $file
     * @return array|string
     */
    public function ossUploadFile($file)
    {

        $resResult = ImageAlias::open($file);
        // 尝试执行
        try {
            $config = Config::pull('alioss'); //获取Oss的配置
            //实例化对象 将配置传入
            $ossClient = new OssClientAlias($config['KeyId'], $config['KeySecret'], $config['EndPoint']);
            //这里是有sha1加密 生成文件名 之后连接上后缀
            $fileName = 'img/' . sha1(date('YmdHis', time()) . uniqid()) . '.' . $resResult->type();
            //执行阿里云上传
            $result = $ossClient->uploadFile($config['Bucket'], $fileName, $file->getInfo()['tmp_name']);
            $arr = [
                //图片地址
                'imgurl' => $result['info']['url'],
                //数据库保存名称
                'dbimg' => $fileName,
            ];
        } catch (OssException $e) {
            $arr = $e->getMessage();
        }
        //将结果输出
        return $arr;
    }



    /**
     * @note 视频文件上传
     * @author: lcn
     * @since: 2019/11/11 11:24
     */
    public function ossUploadVideoFile($file)
    {
        //$resResult = ImageAlias::open($file);
        $name = $file->getInfo()['name'];
        $type = substr($name,strripos($name,'.'),strlen($name));
        //echo $type;exit;
        // 尝试执行
        try {
            $config = Config::pull('alioss'); //获取Oss的配置
            //实例化对象 将配置传入
            $ossClient = new OssClientAlias($config['KeyId'], $config['KeySecret'], $config['EndPoint']);
            //这里是有sha1加密 生成文件名 之后连接上后缀
            $fileName = 'video/' . sha1(date('YmdHis', time()) . uniqid())  . $type;
            //执行阿里云上传
            $result = $ossClient->multiuploadFile($config['Bucket'], $fileName, $file->getInfo()['tmp_name']);
            $arr = [
                //图片地址
                'videourl' => $result['info']['url'],
                //数据库保存名称
                'dbvideo' => $fileName,
            ];
        } catch (OssException $e) {
            $arr = $e->getMessage();
        }
        //将结果输出
        return $arr;
    }
}
