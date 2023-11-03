<?php


namespace app\admin\validate;


use app\common\lib\reCaptcha;
use think\facade\Env;
use think\Validate;

class LoginTokenValidate extends Validate
{
    protected $rule = [
        'g-recaptcha-response|Captcha' => ['require', 'checkCaptcha'],
        'token|Token' => ['require', 'length' => 64, 'alphaNum', 'checkToken'],
    ];

    protected $message = [
        'g-recaptcha-response.require' => 'Captcha invalid.',
        'token.require' => 'Token not empty.',
        'token.alphaNum' => 'Wrong Token format.',
        'token.length' => 'Wrong Token format.',
    ];

    protected function checkCaptcha($value, $rule, $data = []){
        $reCaptcha = new reCaptcha();
        return $reCaptcha->validate($value)['success']?true:'Captcha invalid.';
    }

    protected function checkToken($value, $rule, $data = []){
        return (Env::get('ADMIN.PASSWORD')==$value)
            ?true:'Token invalid.';
    }
}