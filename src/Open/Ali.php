<?php
namespace SNS\Open;

class Ali
{
    const ALI_URI = 'https://openapi.alipay.com/gateway.do';

    public static function getUserInfoParams($appId, $accessToken, $rsaPrivateKey)
    {
        $paramsArray = [
            'app_id' => $appId,
            'format' => 'json',
            'method' => 'alipay.user.userinfo.share',
            'charset' => 'UTF-8',
            'sign_type' => 'RSA2',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'auth_token' => $accessToken,
        ];

        $paramsArray['sign'] = self::getSign($paramsArray, $rsaPrivateKey);

        return $paramsArray;
    }

    private static function getSign(array $data, $rsaPrivateKey) : string
    {
        ksort($data);

        $dataString = '';
        foreach ($data as $key => $value) {
            $dataString = $key . '=' . $value . '&';
        }

        openssl_sign(substr($dataString, 0, -1), $sign, $rsaPrivateKey, OPENSSL_ALGO_SHA256);

        return base64_encode($sign);
    }
}
