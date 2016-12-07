<?php
use Http\Mock\Client as MockClient;

class SNSTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testGetAccessTokenFromWeixin()
    {
        $weixinSuccessResponseBodyArray = [
            'access_token' => 'ACCESS_TOKEN',
            'expires_in' => 7200,
            'refresh_token' => 'REFRESH_TOKEN',
            'openid' => 'OPENID',
            'scope' => 'SCOPE',
        ];
        $weixinErrorResponseBodyArray = [
            'errcode' => 40029,
            'errmsg' => 'invalid code',
        ];
        $weixinNotCompleteResponseBodyArray = [
            'expires_in' => 7200,
            'refresh_token' => 'REFRESH_TOKEN',
            'openid' => 'OPENID',
            'scope' => 'SCOPE',
        ];

        $mockHttpClient = new MockClient();
        $mockHttpClient->addResponse(new \GuzzleHttp\Psr7\Response(200, [], json_encode($weixinSuccessResponseBodyArray)));
        $mockHttpClient->addResponse(new \GuzzleHttp\Psr7\Response(200, [], json_encode($weixinErrorResponseBodyArray)));
        $mockHttpClient->addResponse(new \GuzzleHttp\Psr7\Response(200, [], json_encode($weixinNotCompleteResponseBodyArray)));

        $sns = new SNS\SNS($mockHttpClient, new Http\Message\MessageFactory\GuzzleMessageFactory());

        $tokenInfo = $sns->getAccessTokenFromWeixin('APP_ID', 'APP_SECRET', 'CODE');

        $this->tester->assertEquals($weixinSuccessResponseBodyArray, $tokenInfo);

        $request = $mockHttpClient->getRequests()[0];

        $this->tester->assertEquals('GET', $request->getMethod());
        $this->tester->assertEquals('https', $request->getUri()->getScheme());
        $this->tester->assertEquals('api.weixin.qq.com', $request->getUri()->getHost());
        $this->tester->assertEquals('/sns/oauth2/access_token', $request->getUri()->getPath());
        $this->tester->assertEquals('appid=APP_ID&secret=APP_SECRET&code=CODE&grant_type=authorization_code', $request->getUri()->getQuery());

        $this->tester->assertEquals(false, $sns->getAccessTokenFromWeixin('APP_ID', 'APP_SECRET', 'CODE'));
        $this->tester->assertEquals(false, $sns->getAccessTokenFromWeixin('APP_ID', 'APP_SECRET', 'CODE'));

        $mockHttpClient->addException(new Http\Client\Exception\HttpException('test', $request, new \GuzzleHttp\Psr7\Response()));

        $this->tester->assertEquals(false, $sns->getAccessTokenFromWeixin('APP_ID', 'APP_SECRET', 'CODE'));
    }

    public function testGetUserInfoFromWeixin()
    {
        $weixinSuccessResponseBodyArray = [
            'openid' => 'OPENID',
            'nickname' => 'NICKNAME',
            'sex' => 1,
            'province' => 'PROVINCE',
            'city' => 'CITY',
            'country' => 'COUNTRY',
            'headimgurl' =>  'http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/0',
            'privilege' => [
                'PRIVILEGE1',
                'PRIVILEGE2'
            ],
            'unionid' => 'o6_bmasdasdsad6_2sgVt7hMZOPfL',
        ];
        $weixinErrorResponseBodyArray = [
            'errcode' => 40003,
            'errmsg' => 'invalid openid',
        ];
        $weixinNotCompleteResponseBodyArray = [
            'sex' => 1,
            'province' => 'PROVINCE',
            'city' => 'CITY',
            'country' => 'COUNTRY',
            'privilege' => [
                'PRIVILEGE1',
                'PRIVILEGE2'
            ],
            'unionid' => 'o6_bmasdasdsad6_2sgVt7hMZOPfL',
        ];

        $mockHttpClient = new MockClient();
        $mockHttpClient->addResponse(new \GuzzleHttp\Psr7\Response(200, [], json_encode($weixinSuccessResponseBodyArray)));
        $mockHttpClient->addResponse(new \GuzzleHttp\Psr7\Response(200, [], json_encode($weixinErrorResponseBodyArray)));
        $mockHttpClient->addResponse(new \GuzzleHttp\Psr7\Response(200, [], json_encode($weixinNotCompleteResponseBodyArray)));

        $sns = new SNS\SNS($mockHttpClient, new Http\Message\MessageFactory\GuzzleMessageFactory());

        $userInfo = $sns->getUserInfoFromWeixin('ACCESS_TOKEN', 'OPENID');

        $this->tester->assertEquals($weixinSuccessResponseBodyArray, $userInfo);

        $request = $mockHttpClient->getRequests()[0];

        $this->tester->assertEquals('GET', $request->getMethod());
        $this->tester->assertEquals('https', $request->getUri()->getScheme());
        $this->tester->assertEquals('api.weixin.qq.com', $request->getUri()->getHost());
        $this->tester->assertEquals('/sns/userinfo', $request->getUri()->getPath());
        $this->tester->assertEquals('access_token=ACCESS_TOKEN&openid=OPENID', $request->getUri()->getQuery());

        $this->tester->assertEquals(false, $sns->getUserInfoFromWeixin('ACCESS_TOKEN', 'OPENID'));
        $this->tester->assertEquals(false, $sns->getUserInfoFromWeixin('ACCESS_TOKEN', 'OPENID'));
    }

    public function testGetAccessTokenFromQQ()
    {
        $qqSuccessResponseBodyArray = [
            'access_token' => 'ACCESS_TOKEN',
            'expires_in' => 'EXPIRE_IN',
            'refresh_token' => 'REFRESH_TOKEN',
        ];
        $qqErrorResponseBodyArray = ['code' => 'CODE', 'msg' => 'MSG'];
        $qqNotCompleteResponseBodyArray = [
            'expires_in' => 'EXPIRE_IN',
            'refresh_token' => 'REFRESH_TOKEN',
        ];

        $mockHttpClient = new MockClient();
        $mockHttpClient->addResponse(new \GuzzleHttp\Psr7\Response(200, [], http_build_query($qqSuccessResponseBodyArray)));
        $mockHttpClient->addResponse(new \GuzzleHttp\Psr7\Response(200, [], http_build_query($qqErrorResponseBodyArray)));
        $mockHttpClient->addResponse(new \GuzzleHttp\Psr7\Response(200, [], http_build_query($qqNotCompleteResponseBodyArray)));

        $sns = new SNS\SNS($mockHttpClient, new Http\Message\MessageFactory\GuzzleMessageFactory());

        $tokenInfo = $sns->getAccessTokenFromQQ('APP_ID', 'APP_SECRET', 'CODE', 'REDIRECT_URL');

        $this->tester->assertEquals($qqSuccessResponseBodyArray, $tokenInfo);

        $request = $mockHttpClient->getRequests()[0];

        $this->tester->assertEquals('GET', $request->getMethod());
        $this->tester->assertEquals('https', $request->getUri()->getScheme());
        $this->tester->assertEquals('graph.qq.com', $request->getUri()->getHost());
        $this->tester->assertEquals('/oauth2.0/token', $request->getUri()->getPath());
        $this->tester->assertEquals('grant_type=authorization_code&client_id=APP_ID&client_secret=APP_SECRET&code=CODE&redirect_uri=REDIRECT_URL', $request->getUri()->getQuery());

        $this->tester->assertEquals(false, $sns->getAccessTokenFromQQ('APP_ID', 'APP_SECRET', 'CODE', 'REDIRECT_URL'));
        $this->tester->assertEquals(false, $sns->getAccessTokenFromQQ('APP_ID', 'APP_SECRET', 'CODE', 'REDIRECT_URL'));
    }

    public function testGetUserInfoFromQQ()
    {
        $qqSuccessResponseBodyArray = [
            'ret' => 0,
            'msg' => '',
            'nickname' => 'Peter',
            'figureurl' => 'http => //qzapp.qlogo.cn/qzapp/111111/942FEA70050EEAFBD4DCE2C1FC775E56/30',
            'figureurl_1' => 'http => //qzapp.qlogo.cn/qzapp/111111/942FEA70050EEAFBD4DCE2C1FC775E56/50',
            'figureurl_2' => 'http => //qzapp.qlogo.cn/qzapp/111111/942FEA70050EEAFBD4DCE2C1FC775E56/100',
            'figureurl_qq_1' => 'http => //q.qlogo.cn/qqapp/100312990/DE1931D5330620DBD07FB4A5422917B6/40',
            'figureurl_qq_2' => 'http => //q.qlogo.cn/qqapp/100312990/DE1931D5330620DBD07FB4A5422917B6/100',
            'gender' => '男',
            'is_yellow_vip' => '1',
            'vip' => '1',
            'yellow_vip_level' => '7',
            'level' => '7',
            'is_yellow_year_vip' => '1',
        ];
        $qqErrorResponseBodyArray = ['ret' => 1002, 'msg' => '请先登录'];
        $qqNotCompleteResponseBodyArray = [
            'msg' => '',
            'nickname' => 'Peter',
            'figureurl' => 'http => //qzapp.qlogo.cn/qzapp/111111/942FEA70050EEAFBD4DCE2C1FC775E56/30',
            'figureurl_1' => 'http => //qzapp.qlogo.cn/qzapp/111111/942FEA70050EEAFBD4DCE2C1FC775E56/50',
            'figureurl_2' => 'http => //qzapp.qlogo.cn/qzapp/111111/942FEA70050EEAFBD4DCE2C1FC775E56/100',
            'figureurl_qq_1' => 'http => //q.qlogo.cn/qqapp/100312990/DE1931D5330620DBD07FB4A5422917B6/40',
            'figureurl_qq_2' => 'http => //q.qlogo.cn/qqapp/100312990/DE1931D5330620DBD07FB4A5422917B6/100',
            'gender' => '男',
            'is_yellow_vip' => '1',
            'vip' => '1',
            'yellow_vip_level' => '7',
            'level' => '7',
            'is_yellow_year_vip' => '1',
        ];

        $mockHttpClient = new MockClient();
        $mockHttpClient->addResponse(new \GuzzleHttp\Psr7\Response(200, [], json_encode($qqSuccessResponseBodyArray)));
        $mockHttpClient->addResponse(new \GuzzleHttp\Psr7\Response(200, [], json_encode($qqErrorResponseBodyArray)));
        $mockHttpClient->addResponse(new \GuzzleHttp\Psr7\Response(200, [], json_encode($qqNotCompleteResponseBodyArray)));

        $sns = new SNS\SNS($mockHttpClient, new Http\Message\MessageFactory\GuzzleMessageFactory());

        $tokenInfo = $sns->getUserInfoFromQQ('APP_ID', 'ACCESS_TOKEN', 'OPENID');

        $this->tester->assertEquals($qqSuccessResponseBodyArray, $tokenInfo);

        $request = $mockHttpClient->getRequests()[0];

        $this->tester->assertEquals('GET', $request->getMethod());
        $this->tester->assertEquals('https', $request->getUri()->getScheme());
        $this->tester->assertEquals('graph.qq.com', $request->getUri()->getHost());
        $this->tester->assertEquals('/user/get_simple_userinfo', $request->getUri()->getPath());
        $this->tester->assertEquals('access_token=ACCESS_TOKEN&oauth_consumer_key=APP_ID&openid=OPENID', $request->getUri()->getQuery());

        $this->tester->assertEquals(false, $sns->getUserInfoFromQQ('APP_ID', 'ACCESS_TOKEN', 'OPENID'));
        $this->tester->assertEquals(false, $sns->getUserInfoFromQQ('APP_ID', 'ACCESS_TOKEN', 'OPENID'));
    }

    public function testGetOpenIdFromQQ()
    {
        $qqSuccessResponseBodyString = 'callback( {"client_id":"CLIENT_ID","openid":"OPENID"} );';
        $qqErrorResponseBodyArray = ['code' => 'CODE', 'msg' => 'MSG'];
        $qqNotCompleteResponseBodyArray = [
            'client_id' => 'CLIENT_ID',
        ];

        $mockHttpClient = new MockClient();
        $mockHttpClient->addResponse(new \GuzzleHttp\Psr7\Response(200, [], $qqSuccessResponseBodyString));
        $mockHttpClient->addResponse(new \GuzzleHttp\Psr7\Response(200, [], http_build_query($qqErrorResponseBodyArray)));
        $mockHttpClient->addResponse(new \GuzzleHttp\Psr7\Response(200, [], http_build_query($qqNotCompleteResponseBodyArray)));

        $sns = new SNS\SNS($mockHttpClient, new Http\Message\MessageFactory\GuzzleMessageFactory());

        $openId = $sns->getOpenIdFromQQ('ACCESS_TOKEN');

        $this->tester->assertEquals('OPENID', $openId);

        $request = $mockHttpClient->getRequests()[0];

        $this->tester->assertEquals('GET', $request->getMethod());
        $this->tester->assertEquals('https', $request->getUri()->getScheme());
        $this->tester->assertEquals('graph.qq.com', $request->getUri()->getHost());
        $this->tester->assertEquals('/oauth2.0/me', $request->getUri()->getPath());
        $this->tester->assertEquals('access_token=ACCESS_TOKEN', $request->getUri()->getQuery());

        $this->tester->assertEquals(false, $sns->getOpenIdFromQQ('ACCESS_TOKEN'));
        $this->tester->assertEquals(false, $sns->getOpenIdFromQQ('ACCESS_TOKEN'));
    }

    public function testGetAccessTokenFromWeibo()
    {
        $weiboSuccessResponseBodyArray =  [
            'access_token' => 'ACCESS_TOKEN',
            'expires_in' => 1234,
            'remind_in' => '798114',
            'uid' => '12341234',
        ];
        $weiboErrorResponseBodyArray = [
            'request' => '/location/mobile/get_location.php',
            'error_code' => 21923,
            'error' => 'Not find the relevant data!',
        ];
        $weiboNotCompleteResponseBodyArray =  [
            'expires_in' => 1234,
            'remind_in' => '798114',
            'uid' => '12341234',
        ];

        $mockHttpClient = new MockClient();
        $mockHttpClient->addResponse(new \GuzzleHttp\Psr7\Response(200, [], json_encode($weiboSuccessResponseBodyArray)));
        $mockHttpClient->addResponse(new \GuzzleHttp\Psr7\Response(200, [], json_encode($weiboErrorResponseBodyArray)));
        $mockHttpClient->addResponse(new \GuzzleHttp\Psr7\Response(200, [], json_encode($weiboNotCompleteResponseBodyArray)));

        $sns = new SNS\SNS($mockHttpClient, new Http\Message\MessageFactory\GuzzleMessageFactory());

        $tokenInfo = $sns->getAccessTokenFromWeibo('CLIENT_ID', 'CLIENT_SECRET', 'CODE', 'REDIRECT_URI');

        $this->tester->assertEquals($weiboSuccessResponseBodyArray, $tokenInfo);

        $request = $mockHttpClient->getRequests()[0];

        $this->tester->assertEquals('POST', $request->getMethod());
        $this->tester->assertEquals('https', $request->getUri()->getScheme());
        $this->tester->assertEquals('api.weibo.com', $request->getUri()->getHost());
        $this->tester->assertEquals('/oauth2/access_token', $request->getUri()->getPath());
        $this->tester->assertEquals('', $request->getUri()->getQuery());

        $this->tester->assertEquals(false, $sns->getAccessTokenFromWeibo('CLIENT_ID', 'CLIENT_SECRET', 'CODE', 'REDIRECT_URI'));
        $this->tester->assertEquals(false, $sns->getAccessTokenFromWeibo('CLIENT_ID', 'CLIENT_SECRET', 'CODE', 'REDIRECT_URI'));
    }

    public function testGetUserInfoFromWeibo()
    {
        $weiboSuccessResponseBodyArray = [
            'id' =>  1404376560,
            'screen_name' =>  'zaku',
            'name' =>  'zaku',
            'province' =>  '11',
            'city' =>  '5',
            'location' =>  '北京 朝阳区',
            'description' =>  '人生五十年，乃如梦如幻；有生斯有死，壮士复何憾。',
            'url' =>  'http => //blog.sina.com.cn/zaku',
            'profile_image_url' =>  'http => //tp1.sinaimg.cn/1404376560/50/0/1',
            'domain' =>  'zaku',
            'gender' =>  'm',
            'followers_count' =>  1204,
            'friends_count' =>  447,
            'statuses_count' =>  2908,
            'favourites_count' =>  0,
            'created_at' =>  'Fri Aug 28 00 => 00 => 00 +0800 2009',
            'following' =>  false,
            'allow_all_act_msg' =>  false,
            'geo_enabled' =>  true,
            'verified' =>  false,
            'status' =>  [
                'created_at' =>  'Tue May 24 18 => 04 => 53 +0800 2011',
                'id' =>  11142488790,
                'text' => '我的相机到了。',
                'source' =>  '',
                'favorited' =>  false,
                'truncated' =>  false,
                'in_reply_to_status_id' =>  '',
                'in_reply_to_user_id' =>  '',
                'in_reply_to_screen_name' =>  '',
                'geo' =>  null,
                'mid' =>  '5610221544300749636',
                'annotations' =>  [],
                'reposts_count' =>  5,
                'comments_count' =>  8
            ],
            'allow_all_comment' =>  true,
            'avatar_large' =>  'http => //tp1.sinaimg.cn/1404376560/180/0/1',
            'verified_reason' =>  '',
            'follow_me' =>  false,
            'online_status' =>  0,
            'bi_followers_count' =>  215
        ];
        $weiboErrorResponseBodyArray = [
            'request' => '/location/mobile/get_location.php',
            'error_code' => 21923,
            'error' => 'Not find the relevant data!',
        ];
        $weiboNotCompleteResponseBodyArray = [
            'name' =>  'zaku',
            'province' =>  '11',
            'city' =>  '5',
            'location' =>  '北京 朝阳区',
            'description' =>  '人生五十年，乃如梦如幻；有生斯有死，壮士复何憾。',
            'url' =>  'http => //blog.sina.com.cn/zaku',
            'domain' =>  'zaku',
            'gender' =>  'm',
            'followers_count' =>  1204,
            'friends_count' =>  447,
            'statuses_count' =>  2908,
            'favourites_count' =>  0,
            'created_at' =>  'Fri Aug 28 00 => 00 => 00 +0800 2009',
            'following' =>  false,
            'allow_all_act_msg' =>  false,
            'geo_enabled' =>  true,
            'verified' =>  false,
            'status' =>  [
                'created_at' =>  'Tue May 24 18 => 04 => 53 +0800 2011',
                'id' =>  11142488790,
                'text' => '我的相机到了。',
                'source' =>  '',
                'favorited' =>  false,
                'truncated' =>  false,
                'in_reply_to_status_id' =>  '',
                'in_reply_to_user_id' =>  '',
                'in_reply_to_screen_name' =>  '',
                'geo' =>  null,
                'mid' =>  '5610221544300749636',
                'annotations' =>  [],
                'reposts_count' =>  5,
                'comments_count' =>  8
            ],
            'allow_all_comment' =>  true,
            'avatar_large' =>  'http => //tp1.sinaimg.cn/1404376560/180/0/1',
            'verified_reason' =>  '',
            'follow_me' =>  false,
            'online_status' =>  0,
            'bi_followers_count' =>  215
        ];

        $mockHttpClient = new MockClient();
        $mockHttpClient->addResponse(new \GuzzleHttp\Psr7\Response(200, [], json_encode(['uid' => 1234567890])));
        $mockHttpClient->addResponse(new \GuzzleHttp\Psr7\Response(200, [], json_encode($weiboSuccessResponseBodyArray)));
        $mockHttpClient->addResponse(new \GuzzleHttp\Psr7\Response(200, [], json_encode(['uid' => 1234567890])));
        $mockHttpClient->addResponse(new \GuzzleHttp\Psr7\Response(200, [], json_encode($weiboErrorResponseBodyArray)));
        $mockHttpClient->addResponse(new \GuzzleHttp\Psr7\Response(200, [], json_encode(['uid' => 1234567890])));
        $mockHttpClient->addResponse(new \GuzzleHttp\Psr7\Response(200, [], json_encode($weiboNotCompleteResponseBodyArray)));

        $sns = new SNS\SNS($mockHttpClient, new Http\Message\MessageFactory\GuzzleMessageFactory());

        $userInfo = $sns->getUserInfoFromWeibo('ACCESS_TOKEN');

        $this->tester->assertEquals($weiboSuccessResponseBodyArray, $userInfo);

        $request = $mockHttpClient->getRequests()[0];

        $this->tester->assertEquals('GET', $request->getMethod());
        $this->tester->assertEquals('https', $request->getUri()->getScheme());
        $this->tester->assertEquals('api.weibo.com', $request->getUri()->getHost());
        $this->tester->assertEquals('/2/account/get_uid.json', $request->getUri()->getPath());
        $this->tester->assertEquals('access_token=ACCESS_TOKEN', $request->getUri()->getQuery());

        $request = $mockHttpClient->getRequests()[1];

        $this->tester->assertEquals('GET', $request->getMethod());
        $this->tester->assertEquals('https', $request->getUri()->getScheme());
        $this->tester->assertEquals('api.weibo.com', $request->getUri()->getHost());
        $this->tester->assertEquals('/2/users/show.json', $request->getUri()->getPath());
        $this->tester->assertEquals('access_token=ACCESS_TOKEN&uid=1234567890', $request->getUri()->getQuery());

        $this->tester->assertEquals(false, $sns->getUserInfoFromWeibo('ACCESS_TOKEN'));
        $this->tester->assertEquals(false, $sns->getUserInfoFromWeibo('ACCESS_TOKEN'));
    }
}
