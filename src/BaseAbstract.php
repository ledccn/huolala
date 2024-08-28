<?php

namespace Ledc\Huolala;

use Error;
use Exception;
use RuntimeException;
use Throwable;

/**
 * 货拉拉基础抽象类
 */
abstract class BaseAbstract
{
    /**
     * 货拉拉开放平台授权
     */
    const OAUTH_SERVER = 'https://open.huolala.cn/%s?isSandbox=';
    /**
     * 接入点：货拉拉授权页面
     */
    const OAUTH_AUTHORIZE = 'oauth/authorize';
    /**
     * 接入点：通过API换取access_token
     */
    const OAUTH_TOKEN = 'oauth/token';
    /**
     * 货拉拉服务入口（生产环境）
     */
    const API_PRODUCT_SERVER = 'https://openapi.huolala.cn/v1';
    /**
     * 货拉拉服务入口（沙箱环境）
     */
    const API_SANDBOX_SERVER = 'https://openapi-pre.huolala.cn/v1';
    /**
     * @var AccessToken|null
     */
    protected ?AccessToken $accessToken = null;
    /**
     * 配置项
     * @var Config
     */
    protected Config $config;

    /**
     * 构造函数
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * 获取配置项
     * @return Config
     */
    final public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @return AccessToken|null
     */
    final public function getAccessToken(): ?AccessToken
    {
        return $this->accessToken;
    }

    /**
     * @param AccessToken $accessToken
     */
    final public function setAccessToken(AccessToken $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    /**
     * 获取货拉拉开发平台授权域名
     * @return string
     */
    final public function getOauthServer(): string
    {
        $sandbox = $this->getConfig()->isSandbox() ? 'true' : 'false';
        return self::OAUTH_SERVER . $sandbox;
    }

    /**
     * 货拉拉授权页URL
     * @param string $redirect_uri
     * @return string
     */
    final public function getOauthAuthorize(string $redirect_uri): string
    {
        $oauthUrl = sprintf($this->getOauthServer(), '#/' . self::OAUTH_AUTHORIZE);
        return sprintf("%s&response_type=code&client_id=%s&redirect_uri=%s", $oauthUrl, $this->getConfig()->getAppKey(), $redirect_uri);
    }

    /**
     * 使用code通过API换取access_token
     * @param string $code
     * @param string $grant_type 默认：authorization_code，还有password
     * @return string
     */
    final public function getAccessTokenByCode(string $code, string $grant_type = 'authorization_code'): string
    {
        $params = [
            'grant_type' => $grant_type,
            'client_id' => $this->getConfig()->getAppKey(),
        ];
        if ('authorization_code' === $grant_type) {
            $params['code'] = $code;
        } elseif ('password' === $grant_type) {
            $params['auth_mobile'] = $code;   //如果password模式， code为授权手机号
        }
        $uri = http_build_query($params);
        $oauthUrl = sprintf($this->getOauthServer(), self::OAUTH_TOKEN) . '&' . $uri;
        return $this->httpsRequest($oauthUrl);
    }

    /**
     * 根据刷新令牌获取访问令牌
     * @param string $refresh_token
     * @return string
     */
    final public function refreshAccessToken(string $refresh_token): string
    {
        $params = [
            'grant_type' => 'refresh_token',
            'client_id' => $this->getConfig()->getAppKey(),
            'refresh_token' => $refresh_token,
        ];
        $uri = http_build_query($params);
        $oauthUrl = sprintf($this->getOauthServer(), self::OAUTH_TOKEN) . '&' . $uri;
        return $this->httpsRequest($oauthUrl);
    }

    /**
     * 获取货拉拉服务入口
     * @return string
     */
    final public function getApiServer(): string
    {
        return $this->getConfig()->isSandbox() ? self::API_PRODUCT_SERVER : self::API_SANDBOX_SERVER;
    }

    /**
     * 请求api
     * @param string $apiMethod 接口方法名
     * @param bool $needToken 接口是否需要access_token
     * @param array $apiData 业务数据参数
     * @return mixed
     * @throws Exception|Throwable
     */
    final public function callApi(string $apiMethod, bool $needToken = true, array $apiData = [])
    {
        try {
            $timeStamp = time();
            $reqParams = [
                'app_key' => $this->getConfig()->getAppKey(),
                'timestamp' => $timeStamp,
                'nonce_str' => $this->createUuid(),
                'api_method' => $apiMethod,
                'api_version' => $this->getConfig()->getApiVersion(),
            ];
            if (!empty($apiData)) {
                $reqParams['api_data'] = json_encode($apiData);
            }

            if ($needToken) {
                if (null === $this->getAccessToken()) {
                    throw new RuntimeException('$this->accessToken未实现接口');
                }
                $reqParams['access_token'] = $this->getAccessToken()->get();
            }
            $reqParams['signature'] = $this->createSignature($reqParams, $this->getConfig()->getAppSecret());
            return $this->httpsRequest($this->getApiServer(), $reqParams);
        } catch (Error|Exception|Throwable $e) {
            $ret = [
                'ret' => $e->getCode(),
                'msg' => $e->getMessage(),
                'debug' => $e->getTrace(),
            ];
            throw $e;
        }
    }

    /**
     * 生成uuid
     * @param string $prefix
     * @return string
     */
    final public function createUuid(string $prefix = ""): string
    {
        $chars = md5(uniqid(mt_rand(), true));
        $uuid = substr($chars, 0, 8) . '-'
            . substr($chars, 8, 4) . '-'
            . substr($chars, 12, 4) . '-'
            . substr($chars, 16, 4) . '-'
            . substr($chars, 20, 12);
        return $prefix . $uuid;
    }

    /**
     * 按数组key升序排序（字典排序），组成串并进行md5
     * @param array $params
     * @param string $secret
     * @return string
     */
    final public function createSignature(array $params, string $secret): string
    {
        ksort($params);
        $str = '';
        foreach ($params as $key => $val) {
            if ('' != $val) {
                $str .= $key . '=' . $val . '&';
            }
        }
        $str = substr($str, 0, strlen($str) - 1) . $secret;
        return strtolower(md5($str));
    }

    /**
     * 发送请求
     * @param string $url
     * @param array $post_data
     * @param int $timeout
     * @return mixed
     */
    public function httpsRequest(string $url, array $post_data = [], int $timeout = 5)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        if (!empty($post_data)) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_data));
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        $result = json_decode($output, true);
        curl_close($curl);
        return $result;
    }
}
