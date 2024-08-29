<?php

namespace Ledc\Huolala;

use Throwable;

/**
 * 货拉拉APP业务，API接口
 * @link https://open.huolala.cn/#/doc/api?menu=9&type=3&id=15
 */
class AppApi extends BaseAbstract
{
    /**
     * 获取已开通城市列表
     * @docs 通过此接口可获取到货拉拉货运业务所有已开通的城市列表信息
     * @return array
     * @throws Throwable
     */
    public function getCityList(): array
    {
        return $this->callApi(AppApiMethodEnums::u_city_list, false);
    }

    /**
     * 获取城市可选车型信息
     * @docs 通过此接口可获取城市可选车型信息
     * @param int $cityId
     * @return array
     * @throws Throwable
     */
    public function getCityInfo(int $cityId): array
    {
        return $this->callApi(AppApiMethodEnums::u_city_info, false, ['city_id' => $cityId]);
    }
}
