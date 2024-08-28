<?php

namespace Ledc\Huolala;

use JsonSerializable;

/**
 * 配置项
 */
class Config implements JsonSerializable
{
    /**
     * 应用Key
     * @var string
     */
    protected string $appKey;

    /**
     * 应用Secret
     * @var string
     */
    protected string $appSecret;

    /**
     * 接口版本
     * @var string
     */
    protected string $apiVersion = '1.0';

    /**
     * 是否沙箱环境
     * @var bool
     */
    protected bool $sandbox = false;

    /**
     * 构造函数
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if (property_exists($this, $key) && !is_null($value)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * 获取应用Key
     * @return string
     */
    public function getAppKey(): string
    {
        return $this->appKey;
    }

    /**
     * 获取应用Secret
     * @return string
     */
    public function getAppSecret(): string
    {
        return $this->appSecret;
    }

    /**
     * 获取接口版本
     * @return string
     */
    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    /**
     * 是否沙箱环境
     * @return bool
     */
    public function isSandbox(): bool
    {
        return $this->sandbox;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    /**
     * 转数组
     * @return array
     */
    public function toArray(): array
    {
        return $this->jsonSerialize();
    }

    /**
     * 转字符串
     * @param int $options
     * @return string
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * 转字符串
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }
}
