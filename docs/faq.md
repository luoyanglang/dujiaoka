# 常见问题 FAQ

> 收集整理用户高频问题及解决方案

## 安装部署

### Q1: 安装页面显示 404？

**原因**：
- 运行目录未设置为 `/public`
- 伪静态未配置
- Nginx/Apache 配置错误

**解决方案**：

宝塔面板：
1. 网站设置 → 网站目录 → 运行目录选择 `/public`
2. 网站设置 → 伪静态 → 选择 `Laravel 5`

手动配置 Nginx：
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### Q2: Composer install 很慢或失败？

**解决方案**：

使用国内镜像：
```bash
composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
composer install
```

或使用腾讯云镜像：
```bash
composer config -g repo.packagist composer https://mirrors.cloud.tencent.com/composer/
```

### Q3: 权限错误 Permission denied？

**解决方案**：

```bash
cd /www/wwwroot/dujiaoka
chmod -R 755 ./
chown -R www:www ./
chmod -R 777 storage
chmod -R 777 bootstrap/cache
```

### Q4: 数据库连接失败？

**检查项**：
1. 数据库是否创建
2. `.env` 中数据库配置是否正确
3. 数据库用户权限是否正确
4. MySQL 是否启动

**解决方案**：
```bash
# 测试数据库连接
mysql -h 127.0.0.1 -u dujiaoka -p

# 检查 MySQL 状态
systemctl status mysql
```

## 功能使用

### Q5: 队列任务不执行？

**原因**：
- 队列进程未启动
- Redis 未安装或未启动
- Supervisor 配置错误

**解决方案**：

检查队列进程：
```bash
ps aux | grep queue:work
```

手动启动队列：
```bash
cd /www/wwwroot/dujiaoka
php artisan queue:work
```

配置 Supervisor（推荐）：
```ini
[program:dujiaoka-queue]
command=php /www/wwwroot/dujiaoka/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www
```

### Q6: 邮件发送失败？

**常见原因**：
1. SMTP 配置错误
2. SMTP 服务未开启
3. 防火墙拦截
4. 邮箱密码错误（需要授权码）

**解决方案**：

测试邮件配置：
- 后台 → 配置 → Email Test
- 发送测试邮件

常用邮箱配置：

**QQ 邮箱**：
```
SMTP: smtp.qq.com
端口: 465 或 587
加密: SSL
密码: 授权码（不是QQ密码）
```

**163 邮箱**：
```
SMTP: smtp.163.com
端口: 465 或 994
加密: SSL
密码: 授权码
```

### Q7: 支付回调失败？

**检查项**：
1. 支付配置是否正确
2. 回调地址是否可访问
3. 是否开启 HTTPS（部分支付要求）
4. 防火墙是否开放端口

**解决方案**：

查看支付日志：
- 后台 → 配置 → Security_Log
- 筛选支付请求类型

测试回调地址：
```bash
curl https://你的域名/pay/alipay/notify_url
```

### Q8: 卡密不自动发货？

**原因**：
- 卡密库存不足
- 商品类型设置错误
- 队列未执行

**解决方案**：

1. 检查卡密库存：
   - 后台 → 卡密管理 → 查看未售出卡密数量

2. 检查商品设置：
   - 商品类型必须是"自动发货"
   - 关联的卡密必须存在

3. 检查队列：
   ```bash
   php artisan queue:work
   ```

## 安全相关

### Q9: 如何修改后台路径？

**方法一**：修改 `.env` 文件

```env
ADMIN_ROUTE_PREFIX=/你的自定义路径
```

**方法二**：修改 `config/admin.php`

```php
'route' => [
    'prefix' => env('ADMIN_ROUTE_PREFIX', 'admin'),
],
```

清理缓存：
```bash
php artisan config:clear
php artisan cache:clear
```

### Q10: 如何限制后台访问 IP？

**Nginx 配置**：

```nginx
location /admin {
    allow 1.2.3.4;      # 允许的IP
    allow 5.6.7.0/24;   # 允许的IP段
    deny all;           # 拒绝其他
    
    try_files $uri $uri/ /index.php?$query_string;
}
```

**宝塔面板**：
- 网站设置 → 访问限制 → 添加规则

### Q11: 如何防止订单号被枚举？

**已内置防护**（v2.0.5+）：
- 订单查询限流（默认10次/分钟）
- 订单号格式验证
- 可疑请求日志记录

**配置限流**：
- 后台 → 配置 → 系统设置 → 安全设置

### Q12: 如何查看安全日志？

**查看路径**：
- 后台 → 配置 → Security_Log

**日志类型**：
- 支付请求：所有支付相关请求
- 可疑请求：格式错误、异常访问等

**日志管理**：
- 支持筛选、导出
- 自动清理过期日志
- 可配置保留天数

## 性能优化

### Q13: 网站访问很慢？

**优化方案**：

1. 启用 OPcache：
```ini
opcache.enable=1
opcache.memory_consumption=128
```

2. 使用 Redis 缓存：
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

3. 优化 Composer 自动加载：
```bash
composer dump-autoload --optimize
```

4. 开启 Gzip 压缩（Nginx）：
```nginx
gzip on;
gzip_types text/plain text/css application/json application/javascript;
```

### Q14: 数据库查询慢？

**优化方案**：

1. 添加索引：
```sql
-- 订单表
ALTER TABLE orders ADD INDEX idx_order_sn (order_sn);
ALTER TABLE orders ADD INDEX idx_email (email);

-- 卡密表
ALTER TABLE carmis ADD INDEX idx_goods_id (goods_id);
```

2. 定期清理过期订单：
```sql
DELETE FROM orders WHERE status = -1 AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

3. 优化数据库配置：
```ini
innodb_buffer_pool_size = 256M
query_cache_size = 64M
```

## 升级更新

### Q15: 如何升级到最新版本？

**备份数据**：
```bash
# 备份数据库
mysqldump -u root -p dujiaoka > backup_$(date +%Y%m%d).sql

# 备份文件
tar -czf dujiaoka_backup_$(date +%Y%m%d).tar.gz /www/wwwroot/dujiaoka
```

**升级步骤**：
```bash
cd /www/wwwroot/dujiaoka

# 拉取最新代码
git pull

# 更新依赖
composer install

# 执行升级脚本（如有）
mysql -u root -p dujiaoka < database/sql/upgrade_xxx.sql

# 清理缓存
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Q16: 升级后出现错误？

**回滚方案**：

1. 恢复数据库：
```bash
mysql -u root -p dujiaoka < backup_20241207.sql
```

2. 恢复文件：
```bash
tar -xzf dujiaoka_backup_20241207.tar.gz -C /
```

3. 清理缓存：
```bash
php artisan config:clear
php artisan cache:clear
```

## 其他问题

### Q17: 如何更换域名？

**步骤**：

1. 修改 `.env`：
```env
APP_URL=https://新域名.com
```

2. 修改数据库（如有硬编码）：
```sql
UPDATE admin_settings SET value = REPLACE(value, '旧域名', '新域名');
```

3. 清理缓存：
```bash
php artisan config:clear
php artisan cache:clear
```

4. 更新 SSL 证书

### Q18: 如何备份数据？

**自动备份脚本**：

创建 `backup.sh`：
```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/www/backup/dujiaoka"

# 创建备份目录
mkdir -p $BACKUP_DIR

# 备份数据库
mysqldump -u root -p密码 dujiaoka > $BACKUP_DIR/db_$DATE.sql

# 备份文件
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /www/wwwroot/dujiaoka/storage/app

# 删除30天前的备份
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete
```

添加定时任务（每天凌晨3点）：
```bash
0 3 * * * /www/backup/backup.sh
```

### Q19: 如何迁移到新服务器？

**步骤**：

1. 在新服务器安装环境
2. 导出旧服务器数据：
```bash
mysqldump -u root -p dujiaoka > dujiaoka.sql
tar -czf storage.tar.gz storage/app
```

3. 传输到新服务器：
```bash
scp dujiaoka.sql root@新服务器IP:/tmp/
scp storage.tar.gz root@新服务器IP:/tmp/
```

4. 在新服务器导入：
```bash
mysql -u root -p dujiaoka < /tmp/dujiaoka.sql
tar -xzf /tmp/storage.tar.gz -C /www/wwwroot/dujiaoka/
```

5. 修改 `.env` 配置
6. 清理缓存

### Q20: 如何联系技术支持？

**本项目支持**：
- Telegram: https://t.me/luoyanglang
- GitHub Issues: https://github.com/luoyanglang/dujiaoka/issues

**原作者资源**：
- 原作者 Telegram: https://t.me/dujiaoka
- 原项目 GitHub: https://github.com/assimon/dujiaoka

**注意**：
- ❌ 不提供收费技术支持
- ❌ 警惕冒充诈骗
- ✅ 本项目维护者 Telegram: @luoyanglang

---

**没找到答案？**
- 查看 [完整文档](README.md)
- 加入社区提问
- 提交 GitHub Issue
