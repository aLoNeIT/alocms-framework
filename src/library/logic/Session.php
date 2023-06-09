<?php

declare(strict_types=1);

namespace alocms\logic;

use alocms\facade\ErrCode as ErrCodeFacade;
use alocms\logic\Privilege as PrivilegeLogic;
use alocms\model\Hospital as HospitalModel;
use alocms\model\Relation as RelationModel;
use alocms\model\Role as RoleModel;
use alocms\model\User as UserModel;
use alocms\model\UserEnterprise as UserEnterpriseModel;
use alocms\model\UserSession as UserSessionModel;
use alocms\util\CacheConst;
use alocms\util\CmsException;
use alocms\util\Helper;
use alocms\util\JsonTable;
use think\captcha\Captcha;
use think\facade\Cache as CacheFacade;

/**
 * 会话处理逻辑类
 */
class Session extends Base
{

    /**
     * Session实例对象
     *
     * @var \think\session\Store
     */
    protected $handler = null;

    /** @inheritDoc */
    protected function initialize()
    {
        parent::initialize();
        $this->handler = app('session');
    }

    /** @inheritDoc */
    public static function instance(bool $newInstance = false, array $args = []): static
    {
        /** @var Session $session */
        $session = app('SessionLogic', $args, $newInstance);
        return $session;
    }

    /**
     * 获取用户id
     *
     * @return integer|null
     */
    public function getUser(): ?int
    {
        return $this->handler->get('user_id');
    }

    /**
     * 获取用户名称
     *
     * @return string|null
     */
    public function getUserRealName(): ?string
    {
        return $this->handler->get('user_real_name');
    }

    /** 
     * 获取应用类型
     * 
     * @return integer|null
     */
    public function getAppType(): ?int
    {
        return $this->handler->get('app_type');
    }

    /**
     * 获取角色级别
     *
     * @return JsonTable
     */
    public function getRoleLevel(): JsonTable
    {
        try {
            if (false === $this->handler->has('user_role_level')) {
                $userid  = $this->getUser();
                $appType = $this->getAppType();
                if (!$userid || !$appType) {
                    return ErrCodeFacade::getJError(80);
                }
                $role      = RelationModel::instance()->getRoleByUser($appType, $userid)->fieldRaw(
                    " max(rel_role_level) as max_level , min(rel_role_level) as min_level  "
                )->find();
                $maxLevel  = $role->max_level ?? null;
                $minLevel  = $role->min_level ?? null;
                $roleLevel = [
                    'max_level' => $maxLevel,
                    'min_level' => $minLevel
                ];
                $this->handler->set('user_role_level', $roleLevel);
            }
            return $this->jsonTable->successByData($this->handler->get('user_role_level'));
        } catch (\Exception $ex) {
            return Helper::logListenCritical(
                __CLASS__,
                __FUNCTION__ . ":{$ex->getMessage()}",
                $ex
            );
        }
    }

    /**
     * 获取当前会话对应的所有角色
     *
     * @return JsonTable
     */
    public function getRole(): JsonTable
    {
        try {
            if (false === $this->handler->has('user_role')) {
                $userid  = $this->getUser();
                $appType = $this->getAppType();
                if (!$userid || !$appType) {
                    return ErrCodeFacade::getJError(80);
                }
                $relation = RelationModel::instance()->getRoleByUser($appType, $userid)
                    ->order('rel_role_level desc')
                    ->select();

                $roleMess = [];
                foreach ($relation as $item) {
                    // dump($item->role);
                    $rId = $item->role->r_id ?? 0;
                    $rState = $item->role->r_state ?? 0;
                    if (0 === $rState) {
                        //关闭的角色不可以操作
                        continue;
                    }
                    $rName = $item->role->r_name ?? '';
                    $roleMess[] = [
                        'id'   => $rId,
                        'name' => $rName
                    ];
                }

                $this->handler->set('user_role', $roleMess);
            }
            return $this->jsonTable->successByData($this->handler->get('user_role'));
        } catch (\Throwable $ex) {
            return Helper::logListenCritical(static::class, __FUNCTION__, $ex);
        }
    }
    /**
     * 获取当前会话存储的权限编码集合
     *
     * @return JsonTable,
     */
    public function getFunction(): JsonTable
    {
        if (false === $this->handler->has('function')) {
            $userid  = $this->getUser();
            $appType = $this->getAppType();
            if (!$userid || !$appType) {
                return ErrCodeFacade::getJError(80);
            }
            $jResult = PrivilegeLogic::instance()->getByUser($userid, $appType);
            if ($jResult->isSuccess()) {
                $function = $jResult->data;
                $this->handler->set('function', $function);
            } else {
                return ErrCodeFacade::getJError(752);
            }
        }
        return $this->jsonTable->successByData($this->handler->get('function'));
    }
    /**
     * 获取菜单信息
     *
     * @return JsonTable
     */
    public function getMenu(): JsonTable
    {
        if (false === $this->handler->has('menu')) {
            $userid  = $this->getUser();
            $appType = $this->getAppType();
            if (!$userid || !$appType) {
                return ErrCodeFacade::getJError(80);
            }
            $privilegeMenu = PrivilegeLogic::instance()->getMenuByUser($appType, $userid);
            if ($privilegeMenu->isSuccess()) {
                $menu = $privilegeMenu->data;
                $this->handler->set('menu', $menu);
            } else {
                return ErrCodeFacade::getJError(751);
            }
        }
        return $this->jsonTable->successByData($this->handler->get('menu'));
    }

    /**
     * 检查会话状态
     *
     * @param integer $appType 应用类型
     * @return JsonTable
     */
    public function check(int $appType): JsonTable
    {
        try {
            // 校验应用类型是否一致，避免交差调用接口
            if ($appType !== $this->getAppType()) {
                return ErrCodeFacade::getJError(19);
            }
            $expireIn   = $this->handler->get('expire_in', 0);
            $createTime = $this->handler->get('create_time', 0);
            // 用户id无法取出或者token过期
            if ((\is_null($this->getUser())) || (time() > $createTime + $expireIn)) {
                $this->logout();
                return ErrCodeFacade::getJError(80);
            }
            return $this->jsonTable->success();
        } catch (\Throwable $ex) {
            Helper::logListenCritical(static::class, __FUNCTION__, $ex);
            return ErrCodeFacade::getJError(80);
        }
    }


    /**
     * 登录
     *
     * @param integer $appType 应用类型 1-admin
     * @param string $account 账号
     * @param string $password 密码
     * @return JsonTable
     */
    public function login(int $appType, string $account, string $password): JsonTable
    {
        $loginTimes = 0;
        try {
            //校验登录次数
            $redis  = CacheFacade::store('redis');
            $loginTimes = $redis->get(CacheConst::accountLoginTimes($account, $appType)) ?: 1;
            if ($loginTimes > 5) {
                return ErrCodeFacade::getJError(85);
            }
            $loginTimes++;
            $user = UserModel::instance()->getByAccount($appType, $account)->find();
            //判断密码是否正确
            if (is_null($user)) {
                $redis->set(CacheConst::accountLoginTimes($account, $appType), $loginTimes, 900);
                return ErrCodeFacade::getJError(25, ['name' => '账号不存在']);
            }
            // 判断当前用户状态是否关闭
            if (0 == $user->usr_state) {
                $redis->set(CacheConst::accountLoginTimes($account, $appType), $loginTimes, 900);
                return ErrCodeFacade::getJError(83);
            }
            // 密码比较
            if (Helper::md5Salt($password, $user->usr_salt, true) != $user->usr_pwd) {
                $redis->set(CacheConst::accountLoginTimes($account, $appType), $loginTimes, 900);
                return ErrCodeFacade::getJError(84, ['num' => 6 - $loginTimes]);
            }
            // 提取部分需要返回的数据
            $data = [];
            foreach ([
                'usr_id',
                'usr_img_head_url',
                'usr_mail',
                'usr_mp',
                'usr_login_time',
                'usr_login_num',
                'usr_login_ip',
                'usr_app_type',
                'usr_account',
                'usr_need_change',
                'usr_pwd_update_time',
                'usr_real_name',
                'usr_source',
                'usr_source_pk'
            ] as $item) {
                $data[$item] = $user->$item;
            }
            // 合并其他数据
            $data = array_merge(
                $data,
                [
                    'usr_pwd_need_change' => 0,
                    'usr_need_change' => $user->usr_need_change,
                    'usr_login_time' => $user->usr_login_time ?? 0,
                    'usr_login_num' => $user->usr_login_num ?? 0,
                    'usr_login_ip' => $user->usr_login_ip ?? 0,
                ]
            );
            // 生成token
            if (!($jResult = $this->create($appType, $data))->isSuccess()) {
                return $jResult;
            }
            $token = $jResult->data;
            // 获取菜单
            if (!($jResult = $this->getMenu())->isSuccess()) {
                return $jResult;
            }
            $menu = $jResult->data;
            // 获取拥有权限的功能信息
            if (!($jResult = FunctionLogic::instance()->getByUser($appType, $user->usr_id))->isSuccess()) {
                return $jResult;
            }
            $function = $jResult->data;
            // 获取角色级别
            if (!($jResult = $this->getRoleLevel())->isSuccess()) {
                return $jResult;
            }
            $roleLevel    = $jResult->data ?? [];
            // 获取角色列表
            if (!($jResult = $this->getRole())->isSuccess()) {
                return $jResult;
            }
            $role  = $jResult->data ?? [];
            // 组装用户数据
            $userData = array_merge([
                'user'     => Helper::delPrefixArr($data, 'usr_'),
                'menu'     => $menu,
                'function' => $function,
                'role_level'   => $roleLevel,
                'role'     => $role
            ], $token);

            // 更新用户登录次数和最后一次登录ip
            $user->usr_login_time = time();
            $user->usr_login_num  = $user->usr_login_num + 1;
            $user->usr_login_ip   = $this->app->request->ip();
            $user->save();
            return $this->jsonTable->successByData($userData);
        } catch (\Throwable $ex) {
            // 记录登录次数
            $redis->set(CacheConst::accountLoginTimes($account, $appType), $loginTimes, 900);
            return Helper::logListenCritical(static::class, __FUNCTION__, $ex);
        }
    }

    /**
     * 注销
     *
     * @return JsonTable
     */
    public function logout(): JsonTable
    {
        $this->sessionRecord($this->getAppType(), $this->handler->getId());
        return $this->jsonTable->success();
    }

    /**
     * 会话记录
     *
     * @param integer $appType 应用类型
     * @param string $sessionId 会话id
     * @param array $data 用户数据
     * @return void
     */
    protected function sessionRecord(int $appType, string $sessionId, array $data = []): void
    {
        try {
            // 查询session信息
            $userSession = UserSessionModel::instance()->where(
                [
                    'us_app_type' => $appType,
                    'us_session'  => $sessionId,
                ]
            )->order('us_id desc')->find();
            if (\is_null($userSession)) {
                if (!empty($data)) {
                    // 新session记录，插入
                    UserSessionModel::create(
                        [
                            'us_app_type'    => $appType,
                            'us_session'     => $sessionId,
                            'us_create_time' => $data['create_time'] ?? time(),
                            'us_expire_in'   => $data['expire_in'] ?? 7200,
                            'us_user'        => $data['usr_id'],
                            'us_ip'          => $data['client_ip'],
                        ]
                    );
                }
            } else {
                $userSession->delete();
            }
        } catch (\Throwable $ex) {
            // 该方法自身屏蔽异常，不影响上层调用
            Helper::logListenCritical(static::class, __FUNCTION__, $ex);
        }
    }

    /**
     * 通过refresh_token更新新的数据
     *
     * @param string $refreshToken 刷新用的令牌
     * @return JsonTable
     */
    public function refresh(string $refreshToken): JsonTable
    {
        // 验证refresh_token是否有效，若有效则生成新的token
        $svrRefreshToken = $this->handler->get('refresh_token');
        if ($refreshToken !== $svrRefreshToken) {
            // 这里最好强制等于，避免前端上送空字符串时服务端也为空导致匹配成功
            return ErrCodeFacade::getJError(12);
        }
        // 验证通过，开始生成新的token
        $data = $this->handler->all();
        return $this->create($this->getAppType(), $data);
    }

    /**
     * 创建新的token信息
     *
     * @param integer $appType 应用类型
     * @param array $data 附加的token信息
     * @return JsonTable
     */
    public function create(int $appType = 1, array $data = []): JsonTable
    {
        $refreshToken = Helper::randStr(32);
        $expireIn = $this->app->config->get('system.token.expires_in', 7200);
        $refreshExpireIn = $this->app->config->get('system.token.refresh_expires_in', 7000);

        // 当前会 话退出登录
        $this->logout();
        // 创建新的session，并保存数据
        $token = $this->handler->getId();
        $data  = array_merge(
            $data,
            [
                'client_ip' => $this->app->request->ip(), // 添加登录ip，方便未来对ip源做限制
                'refresh_token' => $refreshToken,
                'create_time' => time(),
                'expire_in' => $expireIn,
                'refresh_expire_in' => $refreshExpireIn,
                'app_type' => $appType
            ]
        );
        // 设置当前会话数据
        $this->handler->setData($data);
        // 会话数据持久化
        $this->sessionRecord($appType, $token, $data);
        return $this->jsonTable->successByData(
            [
                'token' => $token,
                'refresh_token' => $refreshToken,
                'expire_in' => $expireIn,
                'refresh_expire_in' => $expireIn
            ]
        );
    }
}
