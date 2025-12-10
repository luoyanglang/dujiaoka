# Linux 手动部署指南

> 适合有 Linux 经验的用户，完全掌控部署过程

## 系统要求

- Ubuntu 20.04/22.04 或 CentOS 7/8
- 2核2G 内存以上
- 20GB 硬盘空间
- Root 或 sudo 权限

## 一、安装基础环境

### Ubuntu/Debian 系统

```bash
# 更新系统
sudo apt update && sudo apt upgrade -y

# 安装基础工具
sudo apt install -y git curl wget vim unzip
```

### CentOS 系统

```bash
# 更新系统
sudo yum update -y

# 安装基础工具
sudo yum install -y git curl wget vim unzip
```

## 二、安装 Nginx

### Ubuntu/Debian

```bash
sudo apt install -y nginx
sudo systemctl start nginx
sudo systemctl enable nginx
```

### CentOS

```bash
sudo yum install -y nginx
sudo systemctl start nginx
sudo systemctl enable nginx
```

## 三、安装 MySQL

### Ubuntu/Debian

```bash
sudo apt install -y mysql-server
sudo systemctl start mysql
sudo systemctl enable mysql

# 安全配置
sudo mysql_secure_installation
```

### CentOS

```bash
sudo yum install -y mysql-server
sudo systemctl start mysqld
sudo systemctl enable mysqld

# 获取临时密码
sudo grep 'temporary password' /var/log/mysqld.log

# 安全配置
sudo mysql_secure_installation
```

### 创建数据库

```bash
mysql -u root -p

CREATE DATABASE dujiaoka CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'dujiaoka'@'localhost' IDENTIFIED BY '你的密码';
GRANT ALL PRIVILEGES ON dujiaoka.* TO 'dujiaoka'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## 四、安装 PHP 7.4

### Ubuntu/Debian

```bash
# 添加 PHP 仓库
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update

# 安装 PHP 及扩展
sudo apt install -y php7.4-fpm php7.4-cli php7.4-mysql php7.4-mbstring \
php7.4-xml php7.4-curl php7.4-zip php7.4-gd php7.4-bcmath \
php7.4-json php7.4-redis php7.4-opcache

# 启动 PHP-FPM
sudo systemctl start php7.4-fpm
sudo systemctl enable php7.4-fpm
```

### CentOS

```bash
# 添加 Remi 仓库
sudo yum install -y epel-release
sudo yum install -y https://rpms.remirepo.net/enterprise/remi-release-7.rpm

# 启用 PHP 7.4
sudo yum install -y yum-utils
sudo yum-config-manager --enable remi-php74

# 安装 PHP 及扩展
sudo yum install -y php php-fpm php-mysql php-mbstring php-xml \
php-curl php-zip php-gd php-bcmath php-json php-redis php-opcache

# 启动 PHP-FPM
sudo systemctl start php-fpm
sudo systemctl enable php-fpm
```

### 配置 PHP

编辑 `/etc/php/7.4/fpm/php.ini`（Ubuntu）或 `/etc/php.ini`（CentOS）：

```ini
memory_limit = 256M
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 300
```

删除禁用函数，找到 `disable_functions` 行，删除：
```
putenv,proc_open,pcntl_signal,pcntl_alarm
```

重启 PHP-FPM：
```bash
sudo systemctl restart php7.4-fpm  # Ubuntu
sudo systemctl restart php-fpm     # CentOS
```

## 五、安装 Redis

### Ubuntu/Debian

```bash
sudo apt install -y redis-server
sudo systemctl start redis
sudo systemctl enable redis
```

### CentOS

```bash
sudo yum install -y redis
sudo systemctl start redis
sudo systemctl enable redis
```

## 六、安装 Composer

```bash
# 下载 Composer
curl -sS https://getcomposer.org/installer | php

# 移动到全局
sudo mv composer.phar /usr/local/bin/composer

# 配置国内镜像
composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
```

## 七、部署项目

### 1. 克隆项目

```bash
cd /var/www
sudo git clone https://github.com/luoyanglang/dujiaoka.git
cd dujiaoka
```

### 2. 安装依赖

```bash
composer install --no-dev --optimize-autoloader
```

### 3. 设置权限

```bash
sudo chown -R www-data:www-data /var/www/dujiaoka  # Ubuntu
sudo chown -R nginx:nginx /var/www/dujiaoka        # CentOS

chmod -R 755 /var/www/dujiaoka
chmod -R 777 /var/www/dujiaoka/storage
chmod -R 777 /var/www/dujiaoka/bootstrap/cache
```

## 八、配置 Nginx

创建配置文件 `/etc/nginx/sites-available/dujiaoka`（Ubuntu）或 `/etc/nginx/conf.d/dujiaoka.conf`（CentOS）：

```nginx
server {
    listen 80;
    server_name shop.yourdomain.com;
    root /var/www/dujiaoka/public;
    index index.php index.html;

    # 日志
    access_log /var/log/nginx/dujiaoka_access.log;
    error_log /var/log/nginx/dujiaoka_error.log;

    # 字符集
    charset utf-8;

    # 主要配置
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP 处理
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;  # Ubuntu
        # fastcgi_pass 127.0.0.1:9000;  # CentOS
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # 静态文件缓存
    location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 7d;
        add_header Cache-Control "public, immutable";
    }

    # 隐藏文件
    location ~ /\. {
        deny all;
    }

    # Gzip 压缩
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml;
    gzip_min_length 1000;
}
```

启用站点（Ubuntu）：
```bash
sudo ln -s /etc/nginx/sites-available/dujiaoka /etc/nginx/sites-enabled/
```

测试配置：
```bash
sudo nginx -t
sudo systemctl reload nginx
```

## 九、配置 SSL（推荐）

### 使用 Let's Encrypt

```bash
# 安装 Certbot
sudo apt install -y certbot python3-certbot-nginx  # Ubuntu
sudo yum install -y certbot python3-certbot-nginx  # CentOS

# 申请证书
sudo certbot --nginx -d shop.yourdomain.com

# 自动续期
sudo certbot renew --dry-run
```

## 十、访问安装向导

浏览器访问：`http://shop.yourdomain.com/install`

按照提示完成安装。

## 十一、配置队列进程

### 安装 Supervisor

#### Ubuntu/Debian
```bash
sudo apt install -y supervisor
```

#### CentOS
```bash
sudo yum install -y supervisor
sudo systemctl start supervisord
sudo systemctl enable supervisord
```

### 配置队列进程

创建配置文件 `/etc/supervisor/conf.d/dujiaoka-queue.conf`：

```ini
[program:dujiaoka-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/dujiaoka/artisan queue:work --sleep=3 --tries=3 --daemon
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/dujiaoka/storage/logs/queue.log
```

启动队列：
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start dujiaoka-queue:*
```

查看状态：
```bash
sudo supervisorctl status
```

## 十二、配置防火墙

### Ubuntu (UFW)

```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### CentOS (Firewalld)

```bash
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
```

## 十三、性能优化

### 1. 启用 OPcache

编辑 PHP 配置文件，确保：

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
```

### 2. 配置 Redis 缓存

编辑 `.env`：
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### 3. 优化 Nginx

编辑 `/etc/nginx/nginx.conf`：

```nginx
worker_processes auto;
worker_connections 2048;
keepalive_timeout 65;
client_max_body_size 20M;
```

## 十四、安全加固

### 1. 配置 fail2ban

```bash
sudo apt install -y fail2ban  # Ubuntu
sudo yum install -y fail2ban  # CentOS

sudo systemctl start fail2ban
sudo systemctl enable fail2ban
```

### 2. 限制后台访问

在 Nginx 配置中添加：

```nginx
location /admin {
    allow 1.2.3.4;      # 你的IP
    deny all;
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 3. 关闭调试模式

编辑 `.env`：
```env
APP_DEBUG=false
APP_ENV=production
```

## 十五、备份方案

### 创建备份脚本

创建 `/root/backup_dujiaoka.sh`：

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backup/dujiaoka"

mkdir -p $BACKUP_DIR

# 备份数据库
mysqldump -u dujiaoka -p密码 dujiaoka > $BACKUP_DIR/db_$DATE.sql

# 备份文件
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/dujiaoka/storage/app

# 删除30天前的备份
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete
```

添加定时任务：
```bash
crontab -e

# 每天凌晨3点备份
0 3 * * * /root/backup_dujiaoka.sh
```

## 常见问题

### Q1: PHP-FPM 连接失败？

检查 socket 路径是否正确：
```bash
ls -la /var/run/php/
```

### Q2: 权限错误？

重新设置权限：
```bash
sudo chown -R www-data:www-data /var/www/dujiaoka
chmod -R 777 /var/www/dujiaoka/storage
```

### Q3: 队列不执行？

检查 Supervisor：
```bash
sudo supervisorctl status
sudo supervisorctl tail dujiaoka-queue
```

## 下一步

- 查看 [基础配置](config-basic.md) 详细配置系统
- 查看 [常见问题](faq.md) 解决常见问题

---

**需要帮助？**
- Telegram: https://t.me/luoyanglang
- GitHub Issues: https://github.com/luoyanglang/dujiaoka/issues
