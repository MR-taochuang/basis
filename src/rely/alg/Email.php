<?php

namespace rely\alg;

use PHPMailer\PHPMailer\PHPMailer;
use rely\Init;

/**
 * Class Email
 * @package rely\alg
 * @author Mr.taochuang <mr_taochuang@163.com>
 * @date 2019/8/12 8:47
 * 邮件发送类
 */
class Email extends PHPMailer
{

    /**
     * @param string $send_type 发送类型 smtp mail sendmail qmail
     * @param string $host 邮件服务器
     * @param int $port 发送端口号
     * @param string $secure 加密方式
     * @param string $char 字符类型
     * @param int $debug 是否开启debug
     * @param bool $auth 是否开启验证 smtp模式必须开启
     * @return $this
     * 邮件发送初始值
     */
    public function instance($send_type='smtp',$host='smtp.qq.com',$port=465,$secure='ssl',$char='UTF-8',$debug=0,$auth=false){
        $this->SMTPDebug=$debug;
        $type='is'.ucfirst($send_type);
        $this->$type;
        $send_type=='smtp'?$this->SMTPAuth=true:$this->SMTPAuth=$auth;
        $this->Host=$host;
        $this->SMTPSecure=$secure;
        $this->Port=$port;
        $this->CharSet=$char;
        return $this;
    }
    /**
     * @param $title /标题
     * @param $content /内容
     * @return $this
     * 设置邮件内容
     */
    public function setContent($title, $content)
    {
        $this->Subject = $title;
        $this->Body = $content;
        return $this;
    }

    /**
     * @param $name /发件人昵称，示在收件人邮件的发件人邮箱地址前的发件人姓名
     * @param $email /smtp登录的账号 这里填入字符串格式的qq号即可
     * @param $password /smtp登录的密码 使用生成的授权码（就刚才叫你保存的最新的授权码）
     * @return $this
     * 设置发件人信息
     */
    public function fromEmail($name,$email,$password,$hostname=''){
        $this->Hostname=empty($hostname)?Init::http()->host():$hostname;
        $this->FromName=$name;
        $this->Username=$email;
        $this->Password=$password;
        $this->From=$email;
        return $this;
    }
    /**
     * @param $email /收件人邮箱
     * @return $this
     * 设置邮件发送人
     */
    public function toEmail($email)
    {
        $this->addAddress($email, $this->Subject);
        return $this;
    }

    /**
     * 发送邮件
     */
    public function send()
    {
        $this->send();
    }
}