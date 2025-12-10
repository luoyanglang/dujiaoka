# 快速安装指南

> 5分钟快速部署独角数卡，推荐使用宝塔面板

## 前置准备

- ✅ 一台 Linux 服务器（1核1G起）
- ✅ 已安装宝塔面板
- ✅ 域名已解析到服务器（可选）

## 安装步骤

### 1. 安装宝塔面板

如果还没有安装宝塔，执行以下命令：

```bash
# Ubuntu/Debian
wget -O install.sh https://download.bt.cn/install/install-ubuntu_6.0.sh && sudo bash install.sh

# CentOS
yum install -y wget && wget -O install.sh https://download.bt.cn/install/install_6.0.sh && sh install.sh
```

### 2. 安装运行环境

登录宝塔面板，安装以下软件：

```
✅ Nginx 1.20+
✅ MySQL 5.7 或 MariaDB 10.5
✅ PHP 7.4（推荐）或 PHP 8.0
✅ Redis（推荐）
```

### 3. 配置 PHP

在宝塔面板 → 软件商店 → PHP 7.4 → 设置：

#### 安装扩展
```
✅ fileinfo
✅ redis
✅ opcache
```

#### 禁用函数
删除以下函数（在"禁用函数"中删除）：
```
putenv
proc_open
pcntl_signal
pcntl_alarm
```

### 4. 创建数据库

宝塔面板 → 数据库 → 添加数据库：

```
数据库名: dujiaoka
用户名: dujiaoka
密码: [自动生成或自定义]
权限: 本地服务器
字符集: utf8mb4
```

### 5. 创建网站

宝塔面板 → 网站 → 添加站点：

```
域名: 你的域名（如 shop.example.com）
根目录: /www/wwwroot/dujiaoka
PHP版本: PHP-74
数据库: 不创建（已创建）
```

### 6. 下载项目

#### 方法一：Git 下载（推荐）

```bash
cd /www/wwwroot
git clone https://github.com/assimon/dujiaoka.git
cd dujiaoka
```

#### 方法二：手动上传

1. 下载项目压缩包
2. 上传到 `/www/wwwroot/`
3. 解压

### 7. 安装依赖

```bash
cd /www/wwwroot/dujiaoka
composer install
```

如果 composer 很慢，使用国内镜像：
```bash
composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
composer install
```

### 8. 设置权限

```bash
chmod -R 755 /www/wwwroot/dujiaoka
chown -R www:www /www/wwwroot/dujiaoka
chmod -R 777 storage
chmod -R 777 bootstrap/cache
```

### 9. 配置网站

在宝塔面板 → 网站 → 你的站点 → 设置：

#### 网站目录
```
运行目录: /public
```

#### 伪静态
选择 `Laravel 5` 或手动添加：
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

#### SSL 证书（推荐）
- 申请 Let's Encrypt 免费证书
- 或上传自己的证书
- 开启强制 HTTPS

### 10. 访问安装页面

浏览器访问：`http://你的域名/install`

按照安装向导填写信息：

```
数据库地址: 127.0.0.1
数据库端口: 3306
数据库名称: dujiaoka
数据库用户: dujiaoka
数据库密码: [步骤4创建的密码]

Redis地址: 127.0.0.1
Redis端口: 6379
Redis密码: [留空或填写]

管理员账号: admin
管理员密码: [自定义强密码]
```

### 11. 配置队列（重要）

#### 创建队列脚本

在宝塔面板 → 计划任务 → Shell脚本：

```bash
#!/bin/bash
cd /www/wwwroot/dujiaoka
php artisan queue:work --sleep=3 --tries=3 --daemon
```

保存为：`dujiaoka_queue.sh`

#### 添加守护进程

宝塔面板 → 软件商店 → Supervisor → 设置 → 添加守护进程：

```ini
[program:dujiaoka-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /www/wwwroot/dujiaoka/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www
numprocs=1
redirect_stderr=true
stdout_logfile=/www/wwwroot/dujiaoka/storage/logs/queue.log
```

### 12. 完成安装

访问：`http://你的域名/admin`

```
默认账号: admin
默认密码: [安装时设置的密码]
```

⚠️ **重要**：登录后立即修改密码！

## 安装后配置

### 1. 基础设置

后台 → 配置 → 系统设置：

```
✅ 网站标题
✅ 网站关键词
✅ 网站描述
✅ 网站Logo
✅ 订单过期时间
```

### 2. 支付配置

后台 → 配置 → 支付配置：

选择并配置你的支付方式（至少配置一个）

### 3. 邮件配置

后台 → 配置 → 系统设置 → 邮件设置：

```
SMTP服务器
SMTP端口
SMTP用户名
SMTP密码
发件人邮箱
发件人名称
```

### 4. 安全设置 🔒

后台 → 配置 → 系统设置 → 安全设置：

```
订单查询限流: 10次/分钟
邮箱查询限流: 10次/分钟
浏览器查询限流: 20次/分钟
安全日志保留: 30天
```

### 5. 添加商品

后台 → 商品管理 → 商品：

1. 创建商品分组
2. 添加商品
3. 导入卡密

## 常见问题

### 安装页面 404？

检查：
1. 运行目录是否设置为 `/public`
2. 伪静态是否配置
3. `.env` 文件是否存在

### 队列不执行？

检查：
1. Supervisor 是否启动
2. 队列进程是否运行
3. Redis 是否正常

### 支付回调失败？

检查：
1. 是否开启 HTTPS
2. 防火墙是否开放端口
3. 支付配置是否正确

### 邮件发送失败？

检查：
1. SMTP 配置是否正确
2. 是否开启了 SMTP 服务
3. 防火墙是否拦截

## 性能优化

### 启用 OPcache

宝塔面板 → PHP 7.4 → 配置文件：

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
```

### 启用 Redis 缓存

编辑 `.env`：
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### 配置 CDN

将静态资源（CSS、JS、图片）放到 CDN

## 安全建议

1. ✅ 修改默认管理员密码
2. ✅ 修改后台路径（.env 中的 ADMIN_ROUTE_PREFIX）
3. ✅ 开启 HTTPS
4. ✅ 定期备份数据库
5. ✅ 限制后台访问 IP
6. ✅ 关闭调试模式（APP_DEBUG=false）

## 下一步

- 查看 [基础配置](config-basic.md) 详细配置系统
- 查看 [常见问题](faq.md) 解决常见问题

---

**提示**: 遇到问题？
- 查看 [常见问题 FAQ](faq.md)
- Telegram: [https://t.me/luoyanglang](https://t.me/luoyanglang)
- GitHub Issues: [https://github.com/luoyanglang/dujiaoka/issues](https://github.com/luoyanglang/dujiaoka/issues)
