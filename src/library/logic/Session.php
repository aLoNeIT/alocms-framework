<?php

declare(strict_types=1);

namespace alocms\logic;

use alocms\constant\Common as CommonConst;
use alocms\facade\ErrCode as ErrCodeFacade;
use alocms\logic\{Role as RoleLogic, Privilege as PrivilegeLogic, User as UserLogic};
use alocms\model\{Hospital as HospitalModel, Relation as RelationModel, Role as RoleModel, User as UserModel, UserEnterprise as UserEnterpriseModel, UserSession as UserSessionModel};
use alocms\util\{CacheConst, Helper, JsonTable, CmsException};
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
    protected function initialize(): void
    {
        parent::initialize();
        $this->handler = app('session');
    }

    /**
     * 获取session中存储的数据
     *
     * @param string $name session名称
     * @return mixed session数据
     */
    protected function getSessionData(string $name)
    {
        return $this->handler->get($name);
    }

    /**
     * 获取用户id
     *
     * @return integer|null
     */
    public function getUser(): ?int
    {
        $user = $this->getSessionData('user_id');
        if (\is_null($user)) {
            Helper::exception(ErrCodeFacade::getJError(80));
        }
        return $user;
    }

    /**
     * 获取用户名称
     *
     * @return string|null
     */
    public function getUserRealName(): ?string
    {
        return $this->getSessionData('user_real_name');
    }

    /** 
     * 获取应用类型
     * 
     * @return integer|null
     */
    public function getAppType(): ?int
    {
        $appType = $this->getSessionData('app_type');
        if (\is_null($appType)) {
            Helper::exception(ErrCodeFacade::getJError(80));
        }
        return $appType;
    }
    /**
     * 获取集团id
     *
     * @return integer|null
     */
    public function getCorporation(): ?int
    {
        return $this->getSessionData('corporation');
    }
    /**
     * 获取机构id
     *
     * @return integer|null
     */
    public function getOrganization(): ?int
    {
        return $this->getSessionData('organization');
    }

    /**
     * 获取角色级别
     *
     * @return JsonTable 返回JsonTable对象，data节点是一个数组，包含max_level和min_level两个节点
     */
    public function getUserRoleLevel(): JsonTable
    {
        try {
            $userRoleLevel = $this->handler->get('user_role_level');
            if (\is_null($userRoleLevel)) {
                $user = $this->getUser();
                $appType = $this->getAppType();
                if (!($jResult = RoleLogic::instance()->getUserLevel($user, $appType))->isSuccess()) {
                    return $jResult;
                }
                $userRoleLevel = $jResult->data;
                // 保存至session中
                $this->handler->set('user_role_level', $userRoleLevel);
            }
            return $this->jsonTable->successByData($userRoleLevel);
        } catch (\Throwable $ex) {
            return Helper::logListenException(static::class, __FUNCTION__, $ex);
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
            $userRole = $this->handler->get('user_role');
            if (\is_null($userRole)) {
                $user  = $this->getUser();
                $appType = $this->getAppType();
                // 获取用户角色
                if (!($jResult = UserLogic::instance()->getRole($user, $appType))->isSuccess()) {
                    return $jResult;
                }
                $userRole = $jResult->data;
                $this->handler->set('user_role', $userRole);
            }
            return $this->jsonTable->successByData($userRole);
        } catch (\Throwable $ex) {
            return Helper::logListenCritical(static::class, __FUNCTION__, $ex);
        }
    }
    /**
     * 获取当前会话存储的权限编码集合
     *
     * @return JsonTable 返回JsonTable对象，data节点是权限编码集合
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
     * 注销
     *
     * @return JsonTable
     */
    public function logout(): JsonTable
    {
        $this->sessionRecord($this->handler->getId(), [], $this->getAppType());
        return $this->jsonTable->success();
    }

    /**
     * 会话记录
     *
     * @param string $sessionId 会话id
     * @param array $data 用户数据
     * @param integer $appType 应用类型
     * @return void
     */
    protected function sessionRecord(string $sessionId, array $data = [], int $appType = CommonConst::APP_TYPE_ORGANIZATION): void
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
        $this->sessionRecord($token, $data, $appType);
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
