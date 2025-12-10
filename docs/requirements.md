# 环境要求

> 在开始安装前，请确保你的服务器满足以下要求

## 服务器要求

### 操作系统

#### ✅ 推荐系统
- **Ubuntu** 20.04 / 22.04 LTS ⭐推荐
- **Debian** 10 / 11
- **CentOS** 7 / 8 / Stream

#### ⚠️ 可用但不推荐
- Windows Server 2016/2019/2022（仅开发测试）
- macOS（仅开发测试）

#### ❌ 不支持
- 虚拟主机（Shared Hosting）
- 32位系统
- Windows XP/7/8

### 硬件配置

#### 最低配置（小流量）
- CPU: 1核
- 内存: 1GB
- 硬盘: 10GB
- 带宽: 1Mbps

#### 推荐配置（中等流量）
- CPU: 2核
- 内存: 2GB
- 硬盘: 20GB SSD
- 带宽: 5Mbps

#### 高性能配置（大流量）
- CPU: 4核+
- 内存: 4GB+
- 硬盘: 50GB+ SSD
- 带宽: 10Mbps+

## 软件要求

### PHP 环境

#### PHP 版本
- **PHP 7.4** ⭐推荐
- **PHP 8.0** ✅支持
- **PHP 8.1** ✅支持
- PHP 7.2/7.3 ⚠️不推荐
- PHP 8.2+ ❌未测试

#### 必需的 PHP 扩展
```
✅ BCMath
✅ Ctype
✅ Fileinfo
✅ JSON
✅ Mbstring
✅ OpenSSL
✅ PDO
✅ PDO_MySQL
✅ Tokenizer
✅ XML
✅ cURL
✅ GD 或 Imagick
✅ ZIP
```

#### 推荐的 PHP 扩展
```
⭐ Redis - 缓存和队列
⭐ OPcache - 性能优化
⭐ APCu - 额外缓存
```

#### PHP 配置要求
```ini
memory_limit >= 128M
upload_max_filesize >= 20M
post_max_size >= 20M
max_execution_time >= 60
```

#### 必须禁用的函数
确保以下函数未被禁用：
```
exec
passthru
shell_exec
system
proc_open
popen
putenv
pcntl_signal
pcntl_alarm
```

### 数据库

#### MySQL
- **MySQL 5.6+** ✅支持
- **MySQL 5.7** ⭐推荐
- **MySQL 8.0** ✅支持

#### MariaDB
- **MariaDB 10.3+** ✅支持
- **MariaDB 10.5** ⭐推荐

#### 数据库配置
```sql
-- 字符集
character_set_server = utf8mb4
collation_server = utf8mb4_unicode_ci

-- 连接数
max_connections >= 100

-- 包大小
max_allowed_packet >= 16M
```

### Web 服务器

#### Nginx（推荐）
- **Nginx 1.16+** ⭐推荐
- **Nginx 1.18+** ✅支持
- **Nginx 1.20+** ✅支持

#### Apache
- **Apache 2.4+** ✅支持
- 需要启用 mod_rewrite

#### 其他
- **Caddy** ✅支持
- **OpenLiteSpeed** ✅支持

### 缓存服务（可选但推荐）

#### Redis
- **Redis 5.0+** ⭐推荐
- **Redis 6.0+** ✅支持
- **Redis 7.0+** ✅支持

#### Memcached
- **Memcached 1.5+** ✅支持

### 进程管理（生产环境必需）

#### Supervisor
- **Supervisor 3.3+** ⭐推荐
- 用于管理队列进程

#### Systemd
- ✅ 可用于管理队列

### 其他工具

#### Composer
- **Composer 2.x** ⭐推荐
- **Composer 1.x** ✅支持

#### Git（可选）
- 用于版本管理和更新

#### Node.js（可选）
- 用于前端资源编译

## 网络要求

### 端口开放
```
80   - HTTP（必需）
443  - HTTPS（推荐）
3306 - MySQL（内网）
6379 - Redis（内网）
```

### 域名要求
- 建议使用独立域名
- 支持子域名
- 需要备案（中国大陆）

### SSL 证书（推荐）
- Let's Encrypt 免费证书
- 商业 SSL 证书
- 支付接口可能要求 HTTPS

## 权限要求

### 文件权限
```bash
# 需要写权限的目录
storage/          - 755
storage/logs/     - 755
storage/app/      - 755
storage/framework/ - 755
bootstrap/cache/  - 755

# 需要写权限的文件
.env              - 644
```

### 用户权限
- 建议使用非 root 用户运行
- Web 服务器用户需要读取项目文件
- 队列进程用户需要执行 PHP

## 安全要求

### 防火墙
- 开放必要端口
- 限制数据库端口访问
- 配置 fail2ban（推荐）

### SELinux
- 可以禁用或正确配置
- CentOS 默认启用

### 文件上传
- 限制上传文件类型
- 限制上传文件大小
- 配置上传目录权限

## 检查清单

在开始安装前，请确认：

- [ ] 操作系统符合要求
- [ ] PHP 版本正确
- [ ] 所有必需的 PHP 扩展已安装
- [ ] 数据库已安装并运行
- [ ] Web 服务器已安装并配置
- [ ] Composer 已安装
- [ ] 域名已解析（如需要）
- [ ] 防火墙已配置
- [ ] SSL 证书已准备（如需要）

## 环境检测

### 自动检测脚本

创建 `check.php` 文件：

```php
<?php
echo "PHP 版本: " . PHP_VERSION . "\n";
echo "操作系统: " . PHP_OS . "\n\n";

$extensions = [
    'bcmath', 'ctype', 'fileinfo', 'json', 'mbstring',
    'openssl', 'pdo', 'pdo_mysql', 'tokenizer', 'xml',
    'curl', 'gd', 'zip', 'redis'
];

echo "PHP 扩展检查:\n";
foreach ($extensions as $ext) {
    $status = extension_loaded($ext) ? '✅' : '❌';
    echo "$status $ext\n";
}

echo "\nPHP 配置:\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
```

运行检测：
```bash
php check.php
```

### 手动检测

```bash
# 检查 PHP 版本
php -v

# 检查 PHP 扩展
php -m

# 检查 Composer
composer --version

# 检查 MySQL
mysql --version

# 检查 Redis（如已安装）
redis-cli --version

# 检查 Nginx
nginx -v
```

## 🔥推荐服务商






### 面板推荐
- 宝塔面板（最简单）⭐
- 1Panel
- LNMP 一键包

## 下一步

环境准备好后，选择安装方式：

- [宝塔面板部署](install-bt.md) - 推荐新手
- [Linux 手动部署](install-linux.md) - 适合有经验用户
- [Docker 部署](install-docker.md) - 容器化部署
- [快速安装](quick-start.md) - 快速开始

---

**提示**: 如果不确定环境是否满足要求，建议使用宝塔面板部署，它会自动安装所需环境。
