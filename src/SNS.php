<?php
namespace SNS;

use Http\Client\HttpClient;
use Http\Message\MessageFactory;
use Facebook\Facebook;
use SNS\Open\Alipay;
use SNS\Open\QQ;
use SNS\Open\Weibo;
use SNS\Open\Weixin;
use Google_Client;

class SNS
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    public function __construct(HttpClient $httpClient, MessageFactory $messageFactory)
    {
        $this->httpClient = $httpClient;
        $this->messageFactory = $messageFactory;
    }

    public function getAccessTokenFromWeixin($appId, $appSecret, $code)
    {
        $request = $this->messageFactory->createRequest(
            'GET',
            $this->buildQuery(Weixin::getAccessTokenUri(), Weixin::getAccessTokenParams($appId, $appSecret, $code))
        );
        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (\Exception $e) {
            return false;
        }

        $tokenInfo = json_decode($response->getBody(), true);
        if (!is_array($tokenInfo) || isset($tokenInfo['errcode']) || !isset($tokenInfo['access_token'])) {
            return false;
        }

        return $tokenInfo;
    }

    public function getUserInfoFromWeixin($accessToken, $openId)
    {
        $request = $this->messageFactory->createRequest(
            'GET',
            $this->buildQuery(Weixin::getUserInfoUri(), Weixin::getUserInfoParams($accessToken, $openId))
        );
        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (\Exception $e) {
            return false;
        }

        $userInfo = json_decode($response->getBody(), true);
        if (!is_array($userInfo)
            || !isset($userInfo['openid']) || !isset($userInfo['unionid'])
            || !isset($userInfo['nickname']) || !isset($userInfo['headimgurl'])
        ) {
            return false;
        }

        return $userInfo;
    }

    public function getAccessTokenFromQQ($appId, $appSecret, $code, $redirectUrl)
    {
        $request = $this->messageFactory->createRequest(
            'GET',
            $this->buildQuery(QQ::getAccessTokenUri(), QQ::getAccessTokenParams($appId, $appSecret, $code, $redirectUrl))
        );
        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (\Exception $e) {
            return false;
        }

        parse_str($response->getBody(), $tokenInfo);
        if (!is_array($tokenInfo) || !isset($tokenInfo['access_token'])) {
            return false;
        }

        return $tokenInfo;
    }

    public function getUserInfoFromQQ($appId, $accessToken, $openId)
    {
        $request = $this->messageFactory->createRequest(
            'GET',
            $this->buildQuery(QQ::getUserInfoUri(), QQ::getUserInfoParams($appId, $accessToken, $openId))
        );
        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (\Exception $e) {
            return false;
        }

        $userInfo = json_decode($response->getBody(), true);
        if (!is_array($userInfo)
            || !isset($userInfo['ret']) || $userInfo['ret'] != 0
            || !isset($userInfo['nickname']) || !isset($userInfo['figureurl_qq_2'])
        ) {
            return false;
        }

        return $userInfo;
    }

    public function getOpenIdFromQQ($accessToken)
    {
        $request = $this->messageFactory->createRequest(
            'GET',
            $this->buildQuery(QQ::getOpenIdUri(), QQ::getOpenIdParams($accessToken))
        );
        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (\Exception $e) {
            return false;
        }

        if (($position = strpos($response->getBody(), 'callback(')) !== 0) {
            return false;
        }

        $result = json_decode(substr($response->getBody(), 9, -3), true);
        if (!is_array($result) || !isset($result['openid'])) {
            return false;
        }

        return $result['openid'];
    }

    public function getAccessTokenFromWeibo($clientId, $clientSecret, $code, $redirectUrl)
    {
        $request = $this->messageFactory->createRequest(
            'POST',
            Weibo::getAccessTokenUri(),
            [],
            http_build_query(Weibo::getAccessTokenParams($clientId, $clientSecret, $code, $redirectUrl))
        );
        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (\Exception $e) {
            return false;
        }

        $tokenInfo = json_decode($response->getBody(), true);
        if (!is_array($tokenInfo) || isset($tokenInfo['err_code']) || !isset($tokenInfo['access_token'])) {
            return false;
        }

        return $tokenInfo;
    }

    public function getUserInfoFromWeibo($accessToken)
    {
        if (($uid = $this->getUidFromWeibo($accessToken)) === false) {
            return false;
        }

        $request = $this->messageFactory->createRequest(
            'GET',
            $this->buildQuery(Weibo::getUserInfoUri(), Weibo::getUserInfoParams($accessToken, $uid))
        );
        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (\Exception $e) {
            return false;
        }

        $weiboUserInfo = json_decode($response->getBody(), true);
        if (!is_array($weiboUserInfo) || !isset($weiboUserInfo['id']) || !isset($weiboUserInfo['screen_name']) || !isset($weiboUserInfo['profile_image_url'])) {
            return false;
        }

        return $weiboUserInfo;
    }

    private function getUidFromWeibo($accessToken)
    {
        $request = $this->messageFactory->createRequest(
            'GET',
            $this->buildQuery(Weibo::getUidUri(), Weibo::getUidParams($accessToken))
        );
        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (\Exception $e) {
            return false;
        }

        $result = json_decode($response->getBody(), true);
        if (!is_array($result) || !isset($result['uid'])) {
            return false;
        }

        return $result['uid'];
    }

    public function getAccessTokenFromAlipay($appId, $code, $rsaPrivateKey, $signType = 'RSA')
    {
        $request = $this->messageFactory->createRequest(
            'GET',
            $this->buildQuery(Alipay::ALIPAY_URI, Alipay::getAccessTokenParams($appId, $code, $rsaPrivateKey, $signType))
        );

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (\Exception $e) {
            return false;
        }

        $userInfo = json_decode($response->getBody(), true);
        if (!is_array($userInfo) || empty($userInfo['alipay_system_oauth_token_response']['user_id'])) {
            return false;
        }

        return $userInfo['alipay_system_oauth_token_response']['access_token'];
    }

    public function getUserInfoFromAlipay($appId, $accessToken, $rsaPrivateKey, $signType = 'RSA')
    {
        $request = $this->messageFactory->createRequest(
            'GET',
            $this->buildQuery(Alipay::ALIPAY_URI, Alipay::getUserInfoParams($appId, $accessToken, $rsaPrivateKey, $signType))
        );

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (\Exception $e) {
            return false;
        }

        $userInfo = json_decode($response->getBody(), true);
        if (!is_array($userInfo) || empty($userInfo['alipay_user_userinfo_share_response']['user_id'])) {
            return false;
        }

        return $userInfo['alipay_user_userinfo_share_response'];
    }

    private function buildQuery($uri, array $params = [])
    {
        $query = $uri;
        if (!empty($params)) {
            $query .= '?' . http_build_query($params);
        }

        return $query;
    }

    public function getUserInfoFromFacebook($appId, $appSecret, $accessToken)
    {
        $fb = new Facebook(['app_id' => $appId, 'app_secret' => $appSecret]);

        try {
            $response = $fb->get('/me?fields=id,name,picture.type(large)', $accessToken);
        } catch(\Exception $e) {
            return false;
        }

        return $response->getGraphUser()->asArray();
    }

    public function getUserInfoFromGoogle($clientId, $idToken)
    {
        $client = new Google_Client(['client_id' => $clientId]);
        if ($payload = $client->verifyIdToken($idToken)) {
            if (isset($payload['sub'])) {
                return $payload;
            }
        }

        return false;
    }
}
