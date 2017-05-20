<?php
namespace SNS\Open;

class Alipay
{
    const ALIPAY_URI = 'https://openapi.alipay.com/gateway.do';

    public static function getUserInfoParams($appId, $accessToken, $rsaPrivateKey, $signType)
    {
        $paramsArray = [
            'app_id' => $appId,
            'format' => 'json',
            'method' => 'alipay.user.userinfo.share',
            'charset' => 'UTF-8',
            'sign_type' => $signType,
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'auth_token' => $accessToken,
        ];

        $paramsArray['sign'] = self::getSign($paramsArray, $rsaPrivateKey, $signType);

        return $paramsArray;
    }

    private static function getSign(array $data, $rsaPrivateKey, $signType) : string
    {
        ksort($data);

        $dataString = '';
        foreach ($data as $key => $value) {
            $dataString = $key . '=' . $value . '&';
        }

        $dataString = substr($dataString, 0, -1);

        if ($signType == 'RSA2') {
            openssl_sign($dataString, $sign, $rsaPrivateKey, OPENSSL_ALGO_SHA256);
        } else {
            openssl_sign($dataString, $sign, $rsaPrivateKey);
        }

        return base64_encode($sign);
    }
}
