<?php
namespace SNS\Open;

class Weixin
{
    const BASE_URI = 'https://api.weixin.qq.com';
    const USER_INFO_URI = '/sns/userinfo';
    const ACCESS_TOKEN_URI = '/sns/oauth2/access_token';

    const GET_CODE_URI = 'https://open.weixin.qq.com/connect/oauth2/authorize';
    const GET_CODE_URI_PC = 'https://open.weixin.qq.com/connect/qrconnect';

    public static function getUserInfoUri()
    {
        return self::BASE_URI . self::USER_INFO_URI;
    }

    public static function getUserInfoParams($accessToken, $openId)
    {
        return ['access_token' => $accessToken, 'openid' => $openId];
    }

    public static function getCodeUri($appId, $redirectUri, $state)
    {
        $uri = self::GET_CODE_URI . '?';

        foreach (self::getCodeParams($appId, $redirectUri, $state) as $key => $value) {
            $uri .= ($key . '=' . $value . '&');
        }

        return rtrim($uri, '&');
    }

    private static function getCodeParams($appId, $redirectUri, $state)
    {
        return [
            'appid' => $appId,
            'redirect_uri' => urlencode($redirectUri),
            'response_type' => 'code',
            'scope' => 'snsapi_userinfo,snsapi_login',
            'state' => $state,
        ];
    }

    public static function getAccessTokenUri()
    {
        return self::BASE_URI . self::ACCESS_TOKEN_URI;
    }

    public static function getAccessTokenParams($appId, $appSecret, $code)
    {
        return [
            'appid' => $appId,
            'secret' => $appSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
        ];
    }
}
