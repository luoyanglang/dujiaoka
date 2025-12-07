# Windows 服务器部署指南

> ⚠️ **重要提示**：Windows 部署方案仅供特殊需求使用，生产环境强烈推荐使用 Linux 服务器！

## 为什么不推荐 Windows？

- ❌ PHP 性能比 Linux 低 30-50%
- ❌ 缺少成熟的进程管理工具（Supervisor）
- ❌ Redis 官方不支持 Windows
- ❌ 部署和维护成本更高
- ❌ 社区支持少，遇到问题难解决

## 适用场景

- ✅ 本地开发测试
- ✅ 内网演示环境
- ✅ 小流量个人使用
- ✅ 临时测试环境

## 环境要求

### 必需软件
- Windows Server 2016/2019/2022 或 Windows 10/11
- PHP 7.4 或 PHP 8.0+
- MySQL 5.6+ 或 MariaDB 10.3+
- Nginx 或 Apache
- Composer

### 可选软件
- Redis（推荐使用 Memurai 或 Windows 移植版）
- Git for Windows

## 方案一：使用 PhpStudy（推荐新手）

### 1. 下载安装 PhpStudy

访问 [PhpStudy 官网](https://www.xp.cn/) 下载最新版

### 2. 配置环境

```
1. 启动 PhpStudy
2. 切换 PHP 版本到 7.4 或 8.0
3. 启动 MySQL 和 Nginx
4. 安装 Composer（PhpStudy 自带）
```

### 3. 创建站点

```
1. 点击"网站" -> "创建网站"
2. 域名：你的域名或 localhost
3. 根目录：选择项目的 public 目录
4. PHP 版本：7.4 或 8.0
5. 点击"确认"
```

### 4. 配置伪静态

在 PhpStudy 中为站点配置 Nginx 伪静态规则：

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 5. 安装项目

```powershell
# 1. 解压项目到站点目录
# 2. 打开 PowerShell，进入项目目录
cd D:\phpstudy_pro\WWW\dujiaoka

# 3. 安装依赖
composer install

# 4. 配置环境变量
copy .env.example .env

# 5. 生成应用密钥
php artisan key:generate

# 6. 访问安装页面
# http://localhost/install
```

### 6. 配置队列（重要）

**方案 A：同步模式（简单但性能差）**

编辑 `.env` 文件：
```env
QUEUE_CONNECTION=sync
```

**方案 B：使用 Windows 任务计划（推荐）**

1. 创建批处理文件 `queue_worker.bat`：
```batch
@echo off
cd /d D:\phpstudy_pro\WWW\dujiaoka
D:\phpstudy_pro\Extensions\php\php7.4.3nts\php.exe artisan queue:work --sleep=3 --tries=3
```

2. 创建 Windows 任务计划：
```
- 打开"任务计划程序"
- 创建基本任务
- 名称：DujiaokaQueue
- 触发器：系统启动时
- 操作：启动程序
- 程序：D:\phpstudy_pro\WWW\dujiaoka\queue_worker.bat
- 勾选"使用最高权限运行"
```

### 7. 配置 Redis（可选）

**方案 A：使用文件缓存（简单）**
```env
CACHE_DRIVER=file
```

**方案 B：安装 Redis for Windows**

1. 下载 [Redis for Windows](https://github.com/tporadowski/redis/releases)
2. 解压并运行 `redis-server.exe`
3. 配置 `.env`：
```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=
REDIS_PORT=6379
```

4. 设置 Redis 为 Windows 服务：
```powershell
# 以管理员身份运行
redis-server.exe --service-install redis.windows.conf
redis-server.exe --service-start
```

## 方案二：使用 Laragon（推荐开发者）

### 1. 下载安装 Laragon

访问 [Laragon 官网](https://laragon.org/) 下载完整版

### 2. 启动服务

```
1. 启动 Laragon
2. 点击"全部启动"
3. 自动启动 Nginx、MySQL、Redis
```

### 3. 添加项目

```
1. 将项目解压到 C:\laragon\www\dujiaoka
2. 右键 Laragon 托盘图标 -> 快速创建 -> 网站
3. 自动创建虚拟主机：dujiaoka.test
```

### 4. 安装项目

```powershell
# 打开 Laragon 终端
cd dujiaoka
composer install
copy .env.example .env
php artisan key:generate
```

### 5. 访问安装页面

```
http://dujiaoka.test/install
```

## 方案三：手动部署（高级用户）

### 1. 安装 PHP

1. 下载 [PHP 7.4 NTS](https://windows.php.net/download/)
2. 解压到 `C:\php`
3. 配置环境变量：添加 `C:\php` 到 PATH
4. 复制 `php.ini-production` 为 `php.ini`
5. 编辑 `php.ini`，启用扩展：

```ini
extension=curl
extension=fileinfo
extension=gd
extension=mbstring
extension=mysqli
extension=openssl
extension=pdo_mysql
extension=redis
extension=zip
```

### 2. 安装 Composer

```powershell
# 下载安装器
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

# 安装
php composer-setup.php --install-dir=C:\php --filename=composer

# 删除安装器
php -r "unlink('composer-setup.php');"
```

### 3. 安装 Nginx

1. 下载 [Nginx for Windows](http://nginx.org/en/download.html)
2. 解压到 `C:\nginx`
3. 配置 `C:\nginx\conf\nginx.conf`：

```nginx
server {
    listen 80;
    server_name localhost;
    root C:/www/dujiaoka/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
        include        fastcgi_params;
    }
}
```

4. 启动 Nginx：
```powershell
cd C:\nginx
start nginx
```

### 4. 安装 MySQL

1. 下载 [MySQL Installer](https://dev.mysql.com/downloads/installer/)
2. 选择"Developer Default"安装
3. 设置 root 密码
4. 创建数据库：

```sql
CREATE DATABASE dujiaoka CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. 启动 PHP-FPM

创建 `start_php.bat`：
```batch
@echo off
C:\php\php-cgi.exe -b 127.0.0.1:9000 -c C:\php\php.ini
```

### 6. 安装项目

```powershell
cd C:\www\dujiaoka
composer install
copy .env.example .env
php artisan key:generate
```

## 常见问题

### Q1: 队列任务不执行？

**A:** Windows 下推荐使用同步模式或任务计划：

```env
# 方案1：同步模式
QUEUE_CONNECTION=sync

# 方案2：使用任务计划运行队列
# 创建 Windows 任务计划，每分钟执行：
php artisan queue:work --stop-when-empty
```

### Q2: Redis 连接失败？

**A:** 使用文件缓存或安装 Redis for Windows：

```env
# 使用文件缓存
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

### Q3: 权限问题？

**A:** 给予 storage 和 bootstrap/cache 目录写权限：

```powershell
# 右键文件夹 -> 属性 -> 安全 -> 编辑
# 给予 Users 组完全控制权限
```

### Q4: 性能太慢？

**A:** 优化建议：

1. 启用 OPcache（php.ini）：
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
```

2. 使用 Redis 缓存：
```env
CACHE_DRIVER=redis
```

3. 优化 Composer 自动加载：
```powershell
composer dump-autoload --optimize
```

### Q5: 如何设置开机自启？

**A:** 使用 NSSM（Non-Sucking Service Manager）：

```powershell
# 1. 下载 NSSM: https://nssm.cc/download
# 2. 安装服务
nssm install DujiaokaQueue "C:\php\php.exe" "C:\www\dujiaoka\artisan queue:work"
nssm start DujiaokaQueue
```

### Q6: 如何更新项目？

```powershell
# 1. 备份数据库
mysqldump -u root -p dujiaoka > backup.sql

# 2. 更新代码
git pull
# 或手动覆盖文件

# 3. 更新依赖
composer install

# 4. 清理缓存
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# 5. 执行升级脚本（如有）
mysql -u root -p dujiaoka < database/sql/upgrade_xxx.sql
```

## 性能对比

| 环境 | QPS | 响应时间 | 推荐度 |
|------|-----|----------|--------|
| Linux + Nginx | 1000+ | 50ms | ⭐⭐⭐⭐⭐ |
| Windows + Nginx | 300-500 | 150ms | ⭐⭐ |
| Windows + Apache | 200-300 | 200ms | ⭐ |

## 生产环境建议

如果必须使用 Windows 生产环境，建议：

1. ✅ 使用 Windows Server 2019/2022
2. ✅ 配置足够的内存（至少 4GB）
3. ✅ 启用 OPcache 和 Redis
4. ✅ 使用 NSSM 管理队列进程
5. ✅ 定期备份数据库
6. ✅ 配置防火墙和安全策略
7. ✅ 监控服务器性能

## 迁移到 Linux

当业务增长后，建议迁移到 Linux：

```bash
# 1. 导出数据库
mysqldump -u root -p dujiaoka > dujiaoka.sql

# 2. 打包文件
# 压缩 storage/app 目录（上传的文件）

# 3. 在 Linux 服务器上部署
# 参考官方 Linux 部署文档

# 4. 导入数据库
mysql -u root -p dujiaoka < dujiaoka.sql

# 5. 恢复文件
# 解压 storage/app 到对应目录

# 6. 配置 .env
# 修改数据库连接等配置

# 7. 清理缓存
php artisan cache:clear
php artisan config:clear
```

## 技术支持

- 项目文档：[docs/README.md](docs/README.md)
- Telegram：[https://t.me/luoyanglang](https://t.me/luoyanglang)
- GitHub Issues：[提交问题](https://github.com/luoyanglang/dujiaoka/issues)
- 原作者资源：[https://github.com/assimon/dujiaoka](https://github.com/assimon/dujiaoka)

---

**再次提醒**：Windows 部署仅适合开发测试或小流量场景，生产环境请使用 Linux！
