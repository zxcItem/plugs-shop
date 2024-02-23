<?php

declare (strict_types=1);

namespace plugin\shop\controller\api\auth;


use plugin\account\controller\api\Auth;
use plugin\shop\model\ShopConfigPoster;
use plugin\shop\service\PosterService;
use think\admin\Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use WeChat\Exceptions\InvalidResponseException;
use WeChat\Exceptions\LocalCacheException;

/**
 * 推广用户管理
 * @class Spread
 * @package plugin\shop\controller\api\auth
 */
class Spread extends Auth
{


    /**
     * 获取我的海报
     * @return void
     * @throws InvalidResponseException
     * @throws LocalCacheException
     * @throws Exception
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function poster()
    {
        $account = $this->account->get();
        $data = [
            'user.spreat'   => "/pages/home/index?from={$this->unid}",
            'user.headimg'  => $account['user']['headimg'] ?? '',
            'user.nickname' => $account['user']['nickname'] ?? '',
            'user.rolename' => $this->relation['level_name'] ?? '',
        ];
        $items = ShopConfigPoster::items($this->type);
        foreach ($items as &$item) {
            $item['image'] = PosterService::create($item['image'], $item['content'], $data);
            unset($item['content']);
        }
        $this->success('获取海报成功！', $items);
    }
}