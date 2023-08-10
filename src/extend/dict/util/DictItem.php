<?php

declare(strict_types=1);

namespace alocms\extend\dict\util;

use alocms\extend\dict\util\Dict;

/**
 * 字典项类，每个字典项对应表中的一个字段
 * 
 * @property integer $id 字典项id
 * @property integer $dict 字典id
 * @property string $name 字典项中文名
 * @property string $fieldname 字典项对应字段名
 * @property integer $type 字典项类型，1-整数；2-小数；3-日期；4-时间；5-日期时间；6-字符串；7-布尔；8-长字符串；9-图像数据；10-二进制数据
 * @property integer $subtype 字典项子类型 
 *    - 1：整数，0-无；1-颜色；2-货币
 *    - 2：小数，0-无；2-货币
 *    - 3,4,5：日期时间，0-数据库原生时间日期类型；1-Unix时间戳；2-字符串形式,具体字符串格式写入di_select中
 *    - 6：字符串,0-无;1-电话号码;2-手机号码;3-邮政编码;4-电子邮件;5-拼音简码
 *    - 8：长字符串,0-无;1-json
 *    - 9：图像数据,0-无(视为二进制流);1-路径;2：base64
 *    - 10：二进制数据,0-无;1-路径;2:base64
 * @property float $max 字典项最大值
 * @property float $min 字典项最小值，当最小值大于最大值，代表不限制
 * @property integer $pk 是否主键，0-否；1-是
 * @property integer $autoed 是否自增，数值型自增依赖于数据库自增机制，字符串自增通过代码生成，0-否；1-是
 * @property integer $pwded 是否密码字段，0-否；1-是
 * @property string $regex 字典项校验规则
 * @property string $regex_msg 字典项校验失败提示
 * @property string $unit 显示单位
 * @property integer $show_width 显示宽度
 * @property integer $sort 排序规则，奇数asc，偶数desc，数字越小越优先排序
 * @property integer $fuzzy 模糊查询；0-不进行模糊查询；1-全匹配；2-右匹配(str%)；3-左匹配(%str)；4-全匹配(%str%)
 * @property integer $key_dict 外键字典号，代表当前字典项是一个外键，关联另一个字典下的某个字典项
 * @property string $key_table 外键表名，当key_dict不为0时，该字典项必须有值
 * @property string $key_field 外键字段名，当前字典项关联的另一个字典项的fieldname值
 * @property string $key_show 外键显示字段名，一般关联的是另一个字典的主键，显示的是其名称字段
 * @property string $key_join_name 外键表别名，用于生成查询时指定别名
 * @property string $key_join_type 外连方式，left/right/inner
 * @property string $key_condition 外连表达式
 * @property integer $key_visible 外键是否可见，暂无用
 * @property integer $key_width 通过该字典项弹出的对话框的宽度
 * @property integer $key_height 通过该字典项弹出的对话框高度
 * @property integer $link_dict 链接表的字典id
 * @property string $link_table 链接表表名，优先与key_join_name一致，其次是key_table
 * @property string $link_field 链接表字段名，当前字典必须要先有外键字典项才可用，主要用于UI中从外键表提取冗余数据
 * @property integer $show_dict 外显表的字典id，设置了该值，代表当前字典项对应的字段是虚拟字段
 * @property string $show_table 外显表表名，优先与key_join_name一致，其次是key_table
 * @property string $show_field 外显表字段名，当前字典必须要先有外键字典项才可用，主要用于从外键表显示更多字段数据
 * @property string $default 当前字典项默认值
 * @property integer $required 是否必填，非0时，2-新增必填；4-修改是必填；可组合
 * @property integer $readonly 是否只读，非0时，2-新增只读；4-修改只读；可组合
 * @property integer $inputed curd页面是否显示，非0时，1-刷新；2-新增；4-修改；8-读取；16-删除；可组合
 * @property integer $input_width 字典项对应的UI界面输入框长度，0-不限制，组件自动处理；非0代表指定长度px
 * @property integer $show_order 字典项显示顺序，数字越小越靠前，默认1000
 * @property integer $curd 字典项对应的CRUD操作，代表在指定操作时是否处理该字段，1-刷新；2-新增；4-修改；8-读取；16-删除；可组合
 * @property string $group 分组，UI界面上分组显示
 * @property string $select 附加信息，用;分割
 * @property integer $filtered 当前字典项是否在UI界面显示为过滤条件,0-否；1-是
 * @property integer $app_type 应用类型，0-通用；1-管理员
 * @property string $remark 备注
 * 
 * @author alone <alone@alonetech.com>
 */
class DictItem
{
    /**
     * 字典项每个字段的默认值
     */
    const DICTITEM_DEFAULT = [
        'id' => 0,
        'dict' => 0,
        'name' => '',
        'fieldname' => '',
        'type' => 6,
        'subtype' => 0,
        'max' => -1,
        'min' => 0,
        'pk' => 0,
        'autoed' => 0,
        'pwded' => 0,
        'regex' => '',
        'regex_msg' => '',
        'unit' => '',
        'show_width' => 0,
        'sort' => 1000,
        'fuzzy' => 0,
        'key_dict' => 0,
        'key_table' => '',
        'key_field' => '',
        'key_show' => '',
        'key_join_name' => '',
        'key_join_type' => 'left',
        'key_condition' => '',
        'key_visible' => 0,
        'key_width' => 0,
        'key_height' => 0,
        'link_dict' => 0,
        'link_table' => '',
        'link_field' => '',
        'show_dict' => 0,
        'show_table' => '',
        'show_field' => '',
        'default' => '',
        'required' => 0,
        'readonly' => 0,
        'inputed' => 1,
        'input_width' => 0,
        'show_order' => 1000,
        'curd' => 15,
        'group' => '',
        'select' => '',
        'filtered' => 0,
        'app_type' => 0,
        'remark' => '',
    ];

    /**
     * 保存字典项原始数据
     *
     * @var array
     */
    protected $data = [];

    /**
     * 保存的字典对象
     *
     * @var Dict
     */
    protected $parent = null;

    /**
     * 构造函数
     *
     * @param Dict $parent 字典对象
     * @param array $data 字典项原始数据
     */
    public function __construct(Dict $parent, array $data = [])
    {
        $this->parent = $parent;
        $this->load($data);
    }
    /**
     * 魔术方法获取字典项属性值
     *
     * @param string $name
     * @return mix
     */
    public function __get($name)
    {
        if (isset($this->data[strtolower($name)])) {
            return $this->data[strtolower($name)];
        }
    }
    /**
     * 魔术方法设置属性值
     *
     * @param string $name
     * @param mix $value
     */
    public function __set($name, $value)
    {
        if (isset($this->data[strtolower($name)])) {
            $this->data[strtolower($name)] = $value;
        }
    }

    /**
     * 载入数据字典
     * 
     * @param array $data 字典项原始数据
     * @return static 返回当前对象
     */
    public function load(array $data): static
    {
        $this->data = \array_merge(self::DICTITEM_DEFAULT, $data);
        return $this;
    }

    /**
     * 返回字典原始数据
     *
     * @param bool $prefix 是否保留字典项的值前缀
     * @return array 返回字典项数据，数组kv形式
     */
    public function toArray(bool $prefix = false): array
    {
        $data = $this->getData();
        if (false === $prefix) {
            if ($this->key_dict > 0) {
                $data['key_field'] = $this->replacePrefix($data['key_field'], \strtolower(\parse_name($data['key_join_name'] ?: $data['key_table'])));
                $data['key_show'] = $this->replacePrefix($data['key_show'], \strtolower(\parse_name($data['key_join_name'] ?: $data['key_table'])));
            }
            if ($this->link_dict > 0) {
                $data['link_field'] = $this->replacePrefix($data['link_field'], \strtolower(\parse_name($data['link_table'])));
            }
            if ($this->show_dict > 0) {
                $data['show_field'] = $this->replacePrefix($data['show_field'], \strtolower(\parse_name($data['show_table'])));
            }
        }
        return $data;
    }
    /**
     * 替换前缀
     *
     * @param string $key 键名
     * @param string $newPrefix 新前缀
     * @return string 返回处理后的前缀
     */
    protected function replacePrefix(string $key, string $newPrefix = ''): string
    {
        $arr = \explode('_', $key);
        array_shift($arr);
        array_unshift($arr, $newPrefix);
        return \join('_', $arr);
    }

    /**
     * 清除保存的内容
     */
    public function clear(): void
    {
        $this->data = \array_merge($this->data, self::DICTITEM_DEFAULT);
    }
    /**
     * 获取字典项原始配置数据
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
    /**
     * 批量设置字典项数据，只能设置已有的字典项数据
     *
     * @param array $data 字典项数据结构
     * @return static 返回当前字典项对象
     */
    public function setData(array $data): static
    {
        foreach ($data as $key => $value) {
            if (isset($this->data[$key])) {
                $this->data[$key] = $value;
            }
        }
        return $this;
    }
}
