# 宝塔面板部署指南

> 推荐新手使用，最简单的部署方式

## 前置准备

- 一台 Linux 服务器（1核1G起步，推荐2核2G）
- 已安装宝塔面板
- 域名（可选，建议使用）

## 一、安装宝塔面板

### 1. 选择系统对应的安装命令

**Ubuntu/Deepin**
```bash
wget -O install.sh https://download.bt.cn/install/install-ubuntu_6.0.sh && sudo bash install.sh ed8484bec
```

**Debian**
```bash
wget -O install.sh https://download.bt.cn/install/install-ubuntu_6.0.sh && bash install.sh ed8484bec
```

**CentOS**
```bash
yum install -y wget && wget -O install.sh https://download.bt.cn/install/install_6.0.sh && sh install.sh ed8484bec
```

### 2. 记录登录信息

安装完成后会显示：
```
外网面板地址: http://你的IP:8888/xxxxxxxx
内网面板地址: http://127.0.0.1:8888/xxxxxxxx
username: xxxxxxxx
password: xxxxxxxx
```

⚠️ 请妥善保存这些信息！

## 二、安装运行环境

### 1. 登录宝塔面板

浏览器访问：`http://你的IP:8888/xxxxxxxx`

### 2. 安装软件

进入面板后，会自动弹出推荐安装，选择 LNMP 环境：

```
✅ Nginx 1.20 或更高版本
✅ MySQL 5.7 或 MariaDB 10.5（推荐）
✅ PHP 7.4（推荐）或 PHP 8.0
✅ phpMyAdmin（可选）
```

点击"一键安装"，等待安装完成（约10-20分钟）

### 3. 安装 Redis（推荐）

软件商店 → 搜索 Redis → 安装

## 三、配置 PHP

### 1. 安装 PHP 扩展

软件商店 → PHP 7.4 → 设置 → 安装扩展：

```
✅ fileinfo - 文件信息扩展（必需）
✅ redis - Redis 支持（推荐）
✅ opcache - 性能优化（推荐）
```

### 2. 删除禁用函数

PHP 7.4 → 设置 → 禁用函数：

删除以下函数（在列表中找到并删除）：
```
putenv
proc_open
pcntl_signal
pcntl_alarm
```

### 3. 调整 PHP 配置

PHP 7.4 → 设置 → 配置文件，修改：

```ini
memory_limit = 256M
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 300
```

## 四、创建数据库

### 1. 添加数据库

数据库 → 添加数据库：

```
数据库名: dujiaoka
用户名: dujiaoka
密码: [点击生成随机密码]
访问权限: 本地服务器
字符集: utf8mb4
```

⚠️ 记录数据库密码，后面安装时需要！

## 五、创建网站

### 1. 添加站点

网站 → 添加站点：

```
域名: shop.yourdomain.com（或使用IP）
根目录: /www/wwwroot/dujiaoka
FTP: 不创建
数据库: 不创建（已创建）
PHP版本: PHP-74
```

### 2. 配置网站

点击网站名 → 设置：

#### 网站目录
```
运行目录: /public（重要！）
防跨站攻击: 关闭
```

#### 伪静态
选择 `laravel5` 或手动添加：
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

#### SSL 证书（推荐）
- 点击 SSL → Let's Encrypt
- 输入邮箱 → 申请
- 开启"强制HTTPS"

## 六、上传项目文件

### 方法一：Git 克隆（推荐）

终端 → 输入命令：

```bash
cd /www/wwwroot
rm -rf dujiaoka/*
git clone https://github.com/luoyanglang/dujiaoka.git dujiaoka
cd dujiaoka
```

### 方法二：手动上传

1. 下载项目压缩包
2. 文件 → 上传到 `/www/wwwroot/dujiaoka`
3. 解压

## 七、安装项目依赖

### 1. 安装 Composer

如果没有安装 Composer：

```bash
# 下载 Composer
wget https://getcomposer.org/installer -O composer-setup.php
php composer-setup.php
mv composer.phar /usr/local/bin/composer

# 配置国内镜像
composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
```

### 2. 安装依赖

```bash
cd /www/wwwroot/dujiaoka
composer install --no-dev
```

如果遇到内存不足：
```bash
php -d memory_limit=-1 /usr/local/bin/composer install --no-dev
```

## 八、设置权限

```bash
cd /www/wwwroot/dujiaoka
chmod -R 755 ./
chown -R www:www ./
chmod -R 777 storage
chmod -R 777 bootstrap/cache
```

## 九、访问安装向导

### 1. 访问安装页面

浏览器打开：`http://你的域名/install`

### 2. 填写安装信息

#### 数据库配置
```
数据库地址: 127.0.0.1
数据库端口: 3306
数据库名称: dujiaoka
数据库用户: dujiaoka
数据库密码: [步骤四创建的密码]
```

#### Redis 配置
```
Redis地址: 127.0.0.1
Redis端口: 6379
Redis密码: [留空，除非你设置了密码]
```

#### 管理员账号
```
管理员账号: admin
管理员密码: [设置强密码]
```

### 3. 完成安装

点击"开始安装"，等待完成。

## 十、配置队列进程

### 方法一：使用 Supervisor（推荐）

#### 1. 安装 Supervisor

软件商店 → 搜索 Supervisor → 安装

#### 2. 添加守护进程

Supervisor → 添加守护进程：

```ini
名称: dujiaoka-queue
启动用户: www
运行目录: /www/wwwroot/dujiaoka
启动命令: php artisan queue:work --sleep=3 --tries=3 --daemon
进程数量: 1
```

保存并启动。

### 方法二：使用计划任务

计划任务 → Shell脚本 → 添加：

```bash
#!/bin/bash
cd /www/wwwroot/dujiaoka
php artisan queue:work --stop-when-empty
```

执行周期：每分钟

## 十一、后台配置

### 1. 登录后台

访问：`http://你的域名/admin`

```
账号: admin
密码: [安装时设置的密码]
```

### 2. 基础设置

配置 → 系统设置 → 基础设置：

```
✅ 网站标题
✅ 网站Logo
✅ 网站关键词
✅ 网站描述
✅ 订单过期时间（默认5分钟）
```

### 3. 邮件配置

配置 → 系统设置 → 邮件设置：

```
SMTP服务器: smtp.qq.com（QQ邮箱示例）
SMTP端口: 465
SMTP用户名: your@qq.com
SMTP密码: [授权码，不是QQ密码]
发件人邮箱: your@qq.com
发件人名称: 独角数卡
```

测试邮件：配置 → Email Test

### 4. 支付配置

配置 → 支付配置 → 添加支付方式

至少配置一个支付方式才能正常使用。

### 5. 安全设置 🔒

配置 → 系统设置 → 安全设置：

```
订单查询限流: 10次/分钟
邮箱查询限流: 10次/分钟
浏览器查询限流: 20次/分钟
安全日志保留: 30天
```

## 十二、添加商品

### 1. 创建商品分组

商品管理 → 商品分组 → 添加

### 2. 添加商品

商品管理 → 商品 → 添加：

```
商品名称: 示例商品
商品分组: [选择刚创建的分组]
商品类型: 自动发货
商品价格: 10.00
库存: 根据卡密数量
```

### 3. 导入卡密

卡密管理 → 导入卡密：

```
选择商品: [选择刚创建的商品]
卡密格式: 每行一个
是否循环: 否（一次性卡密）
```

粘贴卡密，点击导入。

## 常见问题

### Q1: 安装页面 404？

检查：
1. 运行目录是否设置为 `/public`
2. 伪静态是否配置
3. `.env` 文件是否存在

### Q2: 队列不执行？

检查：
1. Supervisor 是否启动
2. 查看 Supervisor 日志
3. 手动测试：`php artisan queue:work`

### Q3: 支付回调失败？

检查：
1. 是否开启 HTTPS
2. 防火墙是否开放端口
3. 查看安全日志：配置 → Security_Log

### Q4: 权限错误？

重新设置权限：
```bash
cd /www/wwwroot/dujiaoka
chown -R www:www ./
chmod -R 777 storage
chmod -R 777 bootstrap/cache
```

### Q5: Composer 安装慢？

使用国内镜像：
```bash
composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
```

## 性能优化

### 1. 启用 OPcache

PHP 7.4 → 设置 → 配置文件：

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
```

### 2. 启用 Redis 缓存

编辑 `.env`：
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### 3. 开启 Gzip

网站设置 → 配置文件，添加：

```nginx
gzip on;
gzip_types text/plain text/css application/json application/javascript text/xml application/xml;
gzip_min_length 1000;
```

## 安全建议

1. ✅ 修改默认管理员密码
2. ✅ 修改后台路径（.env 中的 ADMIN_ROUTE_PREFIX）
3. ✅ 开启 HTTPS
4. ✅ 定期备份数据库
5. ✅ 限制后台访问 IP（网站设置 → 访问限制）
6. ✅ 关闭调试模式（.env 中 APP_DEBUG=false）

## 下一步

- 查看 [基础配置](config-basic.md) 详细配置系统
- 查看 [常见问题](faq.md) 解决常见问题

---

**遇到问题？**
- 查看 [常见问题](faq.md)
- Telegram: https://t.me/luoyanglang
- GitHub Issues: https://github.com/luoyanglang/dujiaoka/issues
