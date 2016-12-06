<?php
namespace SNS\Open;

class QQ
{
    const BASE_URI = 'https://graph.qq.com';
    const USER_INFO_URI = '/user/get_simple_userinfo';

    const GET_CODE_URI = 'https://graph.qq.com/oauth2.0/authorize';
    const GET_ACCESS_TOKEN_URI = 'https://graph.qq.com/oauth2.0/token';
    const GET_OPEN_ID_URI = 'https://graph.qq.com/oauth2.0/me';

    public static function getUserInfoUri()
    {
        return self::BASE_URI . self::USER_INFO_URI;
    }

    public static function getUserInfoParams($appId, $accessToken, $openId)
    {
        return [
            'access_token' => $accessToken,
            'oauth_consumer_key' => $appId,
            'openid' => $openId,
        ];
    }

    public static function getCodeUri($appId, $redirectUri, $state)
    {
        $uri = self::GET_CODE_URI . '?';

        foreach (self::getCodeParams($appId, $redirectUri, $state) as $key => $value) {
            $uri .= ($key . '=' . $value . '&');
        }

        return rtrim($uri, '&');
    }

    public static function getCodeParams($appId, $redirectUri, $state)
    {
        return [
            'response_type' => 'code',
            'client_id' => $appId,
            'redirect_uri' => urlencode($redirectUri),
            'state' => $state,
        ];
    }

    public static function getAccessTokenUri()
    {
        return self::GET_ACCESS_TOKEN_URI;
    }

    public static function getAccessTokenParams($appId, $appSecret, $code, $redirectUrl)
    {
        return [
            'grant_type' => 'authorization_code',
            'client_id' => $appId,
            'client_secret' => $appSecret,
            'code' => $code,
            'redirect_uri' => $redirectUrl,
        ];
    }

    public static function getOpenIdUri()
    {
        return self::GET_OPEN_ID_URI;
    }

    public static function getOpenIdParams($accessToken)
    {
        return [
            'access_token' => $accessToken,
        ];
    }
}
