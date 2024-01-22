<?php


namespace app\common\lib;


use think\facade\Env;

class reCaptcha
{
    private $recaptcha_url = 'https://www.recaptcha.net/recaptcha/api/siteverify';

    public function validate($response, $ip = ''){
        $data = [
            'secret' => Env::get('RECAPTCHA.SERVER_TOKEN'),
            'response' => $response,
        ];
        return $this->post($data);
    }

    private function post($data) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->recaptcha_url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,TRUE);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl,CURLOPT_TIMEOUT, 5);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return json_decode($output,true);
    }
}