<?php
namespace SNS\Open;

class Weibo
{
    const BASE_URI = 'https://api.weibo.com/2';
    const GET_UID_URI = '/account/get_uid';
    const USER_INFO_URI = '/users/show';

    const GET_CODE_URI = 'https://api.weibo.com/oauth2/authorize';
    const GET_ACCESS_TOKEN_URI = 'https://api.weibo.com/oauth2/access_token';

    const FORMAT_POSTFIX = '.json';

    public static function getUidUri()
    {
        return self::BASE_URI . self::GET_UID_URI . self::FORMAT_POSTFIX;
    }

    public static function getUidParams($accessToken)
    {
        return ['access_token' => $accessToken];
    }

    public static function getUserInfoUri()
    {
        return self::BASE_URI . self::USER_INFO_URI . self::FORMAT_POSTFIX;
    }

    public static function getUserInfoParams($accessToken, $uid)
    {
        return ['access_token' => $accessToken, 'uid' => $uid];
    }

    public static function getCodeUri($clientId, $redirectUri)
    {
        $uri = self::GET_CODE_URI . '?';

        foreach (self::getCodeParams($clientId, $redirectUri) as $key => $value) {
            $uri .= ($key . '=' . $value . '&');
        }

        return rtrim($uri, '&');
    }

    public static function getCodeParams($clientId, $redirectUri)
    {
        return [
            'client_id' => $clientId,
            'response_type' => 'code',
            'redirect_uri' => urlencode($redirectUri),
        ];
    }

    public static function getAccessTokenUri()
    {
        return self::GET_ACCESS_TOKEN_URI;
    }

    public static function getAccessTokenParams($clientId, $clientSecret, $code, $redirectUrl)
    {
        return [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUrl,
        ];
    }
}
