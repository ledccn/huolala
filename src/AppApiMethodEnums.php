<?php

namespace Ledc\Huolala;

/**
 * 货拉拉APP业务，API接口枚举
 */
class AppApiMethodEnums
{
    /**
     * 获取已开通城市列表
     * @docs 通过此接口可获取到货拉拉货运业务所有已开通的城市列表信息
     */
    const u_city_list = 'u-city-list';

    /**
     * 获取城市可选车型信息
     * @docs 通过此接口可获取城市可选车型信息
     */
    const u_city_info = 'u-city-info';

    /**
     * 计价（新版）
     * @docs 如果使用优惠券进行计价，需要使用新版计价接口
     */
    const u_price_calculate = 'u-price-calculate';
}
