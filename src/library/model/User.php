<?php

declare(strict_types=1);

namespace alocms\model;

use think\db\Query;
use think\model\concern\SoftDelete;
use think\model\relation\HasMany;
use think\model\relation\HasOne;

/**
 * 用户模型
 */
class User extends Base
{
    use SoftDelete;
    /** @inheritDoc */
    protected $table = '{$database_prefix}_user';
    /** @inheritDoc */
    protected $pk = 'usr_id';
    /** @inheritDoc */
    protected $prefix = 'usr_';
    /** @inheritDoc */
    protected $createTime = 'usr_create_time';
    /** @inheritDoc */
    protected $updateTime = 'usr_update_time';
    /** @inheritDoc */
    protected $deleteTime = 'usr_delete_time';
    /** @inheritDoc */
    protected $defaultSoftDelete = 0;

    // 设置字段信息
    protected $schema = [
        'usr_id' => 'int',
        'usr_app_type' => 'int',
        'usr_hospital' => 'int',
        'usr_login_time' => 'int',
        'usr_login_num' => 'int',
        'usr_img_head_file' => 'int',
        'usr_img_head_url' => 'string',
        'usr_need_change' => 'int',
        'usr_pwd_update_time' => 'int',
        'usr_state' => 'int',
        'usr_sex' => 'string',
        'usr_salt' => 'string',
        'usr_mp' => 'string',
        'usr_account' => 'string',
        'usr_pwd' => 'string',
        'usr_real_name' => 'string',
        'usr_tcc_account' => 'string',
        'usr_wwx_account' => 'string',
        'usr_mail' => 'string',
        'usr_cert_type' => 'string',
        'usr_cert_code' => 'string',
        'usr_cert_front_file' => 'int',
        'usr_cert_reverse_file' => 'int',
        'usr_source' => 'string',
        'usr_source_pk' => 'string',
        'usr_remark' => 'string',
        'usr_login_ip' => 'string',
        'usr_create_user' => 'int',
        'usr_create_time' => 'int',
        'usr_update_time' => 'int',
        'usr_delete_time' => 'int',
    ];

    /**
     * 用户角色关联模型
     *
     * @return HasMany
     */
    public function relation(): HasMany
    {
        return $this->hasMany(Relation::class, 'rel_user', 'usr_id');
    }
    /**
     * 机构关联模型
     *
     * @return HasOne
     */
    public function organization(): HasOne
    {
        return $this->hasOne(Organization::class, 'org_id', 'usr_organization');
    }
    /**
     * 集团关联模型
     *
     * @return HasOne
     */
    public function corporation(): HasOne
    {
        return $this->hasOne(Corporation::class, 'corp_id', 'usr_corporation');
    }

    /**
     * 查找指定账户用户信息
     *
     * @param string $account 账号
     * @param int $organization 机构id
     * @param int $corporation 集团id
     * @param int $appType 应用类型
     * @return Query
     */
    public function getByAccount(string $account, ?int $organization = null, ?int $corporation = null, int $appType): Query
    {
        $query =  $this->baseAppTypeQuery($appType)
            ->where('usr_account', $account);
        $query = \is_null($organization) ? $query : $query->where('usr_organization', $organization);
        $query = \is_null($corporation) ? $query : $query->where('usr_corporation', $corporation);
        return $query;
    }

    /**
     * 查询指定手机号用户信息
     *
     * @param string $mp 手机号
     * @param integer $appType 应用类型
     * @return Query
     */
    public function getByMp(string $mp, int $appType): Query
    {
        return $this->baseAppTypeQuery($appType)
            ->where('usr_mp', $mp);
    }
}
