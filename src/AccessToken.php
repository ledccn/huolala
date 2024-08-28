<?php

namespace Ledc\Huolala;

/**
 * AccessToken接口
 */
interface AccessToken
{
    /**
     * 构造函数
     * @param BaseAbstract $abstract
     */
    public function __construct(BaseAbstract $abstract);

    /**
     * 获取
     * @docs 1.从缓存取access_token；2.过期是刷新access_token；3.凭code换取token
     * @return string
     */
    public function get(): string;

    /**
     * 设置缓存
     * @param string $app_key 应用Key
     * @param array $data access_token和refresh_token等数据
     * @param bool $sandbox 是否沙箱环境
     * @return void
     */
    public function set(string $app_key, array $data, bool $sandbox = false): void;
}
