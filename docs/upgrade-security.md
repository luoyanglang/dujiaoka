# 安全功能升级说明

## 版本：2.0.5

## 新增功能

### 1. 安全日志系统
- 记录所有支付请求
- 记录可疑请求（订单号格式错误等）
- 后台可查看、筛选、导出日志
- 自动清理过期日志

### 2. 动态限流配置
- 订单查询限流（默认10次/分钟）
- 邮箱查询限流（默认10次/分钟）
- 浏览器查询限流（默认20次/分钟）
- 后台可动态调整，无需重启

### 3. XSS 防护增强
- 邮件模板变量自动转义
- 订单信息输出转义
- 输入格式验证

## 升级步骤

### 新安装用户
直接安装即可，所有功能已包含在 `database/sql/install.sql` 中。

### 已安装用户（支持原版安装无缝升级）

#### 方法一：执行升级SQL（推荐）
```bash
# 1. 备份数据库
mysqldump -u用户名 -p 数据库名 > backup_$(date +%Y%m%d).sql

# 2. 执行升级脚本
mysql -u用户名 -p 数据库名 < database/sql/upgrade_security_feature.sql

# 3. 清理缓存
php artisan cache:clear
php artisan config:clear

# 4. 重启队列进程（如果使用）
supervisorctl restart danbao:*
```

#### 方法二：手动升级
1. 在数据库中执行 `database/sql/upgrade_security_feature.sql` 的内容
2. 清理缓存
3. 重启服务

## 配置说明

### 后台配置路径
**配置 -> 系统设置 -> 安全设置**

### 配置项说明
- **订单查询限流**：每分钟允许查询订单的次数（1-100）
- **邮箱查询限流**：每分钟允许通过邮箱查询的次数（1-100）
- **浏览器查询限流**：每分钟允许通过浏览器查询的次数（1-100）
- **安全日志保留天数**：日志保留天数，超过自动清理（1-365天）

### 查看安全日志
**配置 -> Security_Log**

可以：
- 查看所有支付请求和可疑请求
- 按类型、IP、订单号筛选
- 导出日志为Excel
- 批量删除日志

## 注意事项

1. **首次配置**：升级后请在系统设置中配置限流参数，否则使用默认值
2. **日志清理**：建议定期清理旧日志，避免数据库过大
3. **性能影响**：日志记录对性能影响极小，可放心使用
4. **隐私保护**：日志包含IP和请求参数，请妥善保管

## 默认值

```php
throttle_order_search = 10      // 订单查询：10次/分钟
throttle_email_search = 10      // 邮箱查询：10次/分钟
throttle_browser_search = 20    // 浏览器查询：20次/分钟
security_log_keep_days = 30     // 日志保留：30天
```

## 常见问题

### Q: 升级后限流不生效？
A: 清理缓存 `php artisan cache:clear` 并重启服务

### Q: 日志表太大怎么办？
A: 在后台安全日志页面点击"清理旧日志"按钮，或减少日志保留天数

### Q: 如何关闭日志记录？
A: 不建议关闭，如需关闭请修改 `app/Http/Middleware/PayGateWay.php`

### Q: 限流值设置多少合适？
A: 根据实际情况调整：
- 小站点：10次/分钟
- 中等站点：20-50次/分钟
- 大站点：50-100次/分钟

## 技术支持

如有问题：
- GitHub Issues: [https://github.com/luoyanglang/dujiaoka/issues](https://github.com/luoyanglang/dujiaoka/issues)
- Telegram: [https://t.me/luoyanglang](https://t.me/luoyanglang)

原作者项目：[https://github.com/assimon/dujiaoka](https://github.com/assimon/dujiaoka)
