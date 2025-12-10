# Docker 部署指南

> 容器化部署，快速、隔离、易于管理

## 前置要求

- 已安装 Docker
- 已安装 Docker Compose
- 基本的 Docker 知识

## 一、安装 Docker

### Ubuntu/Debian

```bash
# 卸载旧版本
sudo apt remove docker docker-engine docker.io containerd runc

# 安装依赖
sudo apt update
sudo apt install -y apt-transport-https ca-certificates curl gnupg lsb-release

# 添加 Docker 官方 GPG 密钥
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

# 添加 Docker 仓库
echo "deb [arch=amd64 signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# 安装 Docker
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# 启动 Docker
sudo systemctl start docker
sudo systemctl enable docker
```

### CentOS

```bash
# 卸载旧版本
sudo yum remove docker docker-client docker-client-latest docker-common docker-latest docker-latest-logrotate docker-logrotate docker-engine

# 安装依赖
sudo yum install -y yum-utils

# 添加 Docker 仓库
sudo yum-config-manager --add-repo https://download.docker.com/linux/centos/docker-ce.repo

# 安装 Docker
sudo yum install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# 启动 Docker
sudo systemctl start docker
sudo systemctl enable docker
```

### 验证安装

```bash
docker --version
docker compose version
```

## 二、准备项目文件

### 1. 克隆项目

```bash
git clone https://github.com/luoyanglang/dujiaoka.git
cd dujiaoka
```

### 2. 创建 Docker Compose 配置

创建 `docker-compose.yml`：

```yaml
version: '3.8'

services:
  # Nginx
  nginx:
    image: nginx:alpine
    container_name: dujiaoka-nginx
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./:/var/www/html
      - ./docker/nginx/nginx.conf:/etc/nginx/conf.d/default.conf
      - ./docker/nginx/ssl:/etc/nginx/ssl
    depends_on:
      - php
    networks:
      - dujiaoka-network
    restart: unless-stopped

  # PHP-FPM
  php:
    image: php:7.4-fpm
    container_name: dujiaoka-php
    volumes:
      - ./:/var/www/html
      - ./docker/php/php.ini:/usr/local/etc/php/php.ini
    depends_on:
      - mysql
      - redis
    networks:
      - dujiaoka-network
    restart: unless-stopped
    command: >
      sh -c "
      docker-php-ext-install pdo pdo_mysql mysqli bcmath &&
      docker-php-ext-enable opcache &&
      pecl install redis &&
      docker-php-ext-enable redis &&
      php-fpm
      "

  # MySQL
  mysql:
    image: mysql:5.7
    container_name: dujiaoka-mysql
    environment:
      MYSQL_ROOT_PASSWORD: root_password
      MYSQL_DATABASE: dujiaoka
      MYSQL_USER: dujiaoka
      MYSQL_PASSWORD: dujiaoka_password
    volumes:
      - mysql-data:/var/lib/mysql
    ports:
      - "3306:3306"
    networks:
      - dujiaoka-network
    restart: unless-stopped

  # Redis
  redis:
    image: redis:alpine
    container_name: dujiaoka-redis
    ports:
      - "6379:6379"
    volumes:
      - redis-data:/data
    networks:
      - dujiaoka-network
    restart: unless-stopped

  # 队列进程
  queue:
    image: php:7.4-fpm
    container_name: dujiaoka-queue
    volumes:
      - ./:/var/www/html
    depends_on:
      - mysql
      - redis
    networks:
      - dujiaoka-network
    restart: unless-stopped
    command: php /var/www/html/artisan queue:work --sleep=3 --tries=3

volumes:
  mysql-data:
  redis-data:

networks:
  dujiaoka-network:
    driver: bridge
```

### 3. 创建 Nginx 配置

创建 `docker/nginx/nginx.conf`：

```nginx
server {
    listen 80;
    server_name localhost;
    root /var/www/html/public;
    index index.php index.html;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass php:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\. {
        deny all;
    }

    gzip on;
    gzip_types text/plain text/css application/json application/javascript;
}
```

### 4. 创建 PHP 配置

创建 `docker/php/php.ini`：

```ini
memory_limit = 256M
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 300

opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
```

## 三、启动容器

```bash
# 构建并启动
docker compose up -d

# 查看状态
docker compose ps

# 查看日志
docker compose logs -f
```

## 四、安装项目

### 1. 进入 PHP 容器

```bash
docker compose exec php bash
```

### 2. 安装 Composer

```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
```

### 3. 安装依赖

```bash
cd /var/www/html
composer install --no-dev
```

### 4. 设置权限

```bash
chmod -R 777 storage
chmod -R 777 bootstrap/cache
```

### 5. 退出容器

```bash
exit
```

## 五、访问安装向导

浏览器访问：`http://localhost/install`

填写配置信息：

```
数据库地址: mysql
数据库端口: 3306
数据库名称: dujiaoka
数据库用户: dujiaoka
数据库密码: dujiaoka_password

Redis地址: redis
Redis端口: 6379
Redis密码: [留空]
```

## 六、常用命令

### 容器管理

```bash
# 启动
docker compose up -d

# 停止
docker compose stop

# 重启
docker compose restart

# 删除容器
docker compose down

# 删除容器和数据
docker compose down -v
```

### 查看日志

```bash
# 所有服务
docker compose logs -f

# 特定服务
docker compose logs -f nginx
docker compose logs -f php
docker compose logs -f mysql
```

### 进入容器

```bash
# PHP 容器
docker compose exec php bash

# MySQL 容器
docker compose exec mysql bash

# Nginx 容器
docker compose exec nginx sh
```

### 执行命令

```bash
# 清理缓存
docker compose exec php php artisan cache:clear

# 查看队列状态
docker compose exec php php artisan queue:work --once

# 数据库迁移
docker compose exec php php artisan migrate
```

## 七、数据备份

### 备份数据库

```bash
docker compose exec mysql mysqldump -u dujiaoka -pdujiaoka_password dujiaoka > backup_$(date +%Y%m%d).sql
```

### 备份文件

```bash
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/app
```

### 恢复数据库

```bash
docker compose exec -T mysql mysql -u dujiaoka -pdujiaoka_password dujiaoka < backup_20241207.sql
```

## 八、性能优化

### 1. 限制资源使用

修改 `docker-compose.yml`：

```yaml
services:
  php:
    deploy:
      resources:
        limits:
          cpus: '1.0'
          memory: 512M
        reservations:
          cpus: '0.5'
          memory: 256M
```

### 2. 使用 Redis 缓存

编辑 `.env`：
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=redis
REDIS_PORT=6379
```

## 九、生产环境配置

### 1. 使用 HTTPS

将 SSL 证书放到 `docker/nginx/ssl/`：

```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    
    ssl_certificate /etc/nginx/ssl/cert.pem;
    ssl_certificate_key /etc/nginx/ssl/key.pem;
    
    # ... 其他配置
}

server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}
```

### 2. 配置环境变量

创建 `.env.production`：

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_HOST=mysql
DB_DATABASE=dujiaoka
DB_USERNAME=dujiaoka
DB_PASSWORD=强密码

REDIS_HOST=redis
```

### 3. 使用外部数据库

如果使用外部 MySQL 和 Redis，修改 `docker-compose.yml`，移除 mysql 和 redis 服务，并修改 `.env`：

```env
DB_HOST=外部数据库地址
REDIS_HOST=外部Redis地址
```

## 十、故障排查

### 容器无法启动

```bash
# 查看详细日志
docker compose logs

# 检查端口占用
netstat -tulpn | grep :80
netstat -tulpn | grep :3306
```

### 权限问题

```bash
# 进入容器修复权限
docker compose exec php bash
chmod -R 777 storage
chmod -R 777 bootstrap/cache
```

### 数据库连接失败

```bash
# 检查 MySQL 容器
docker compose exec mysql mysql -u root -p

# 测试连接
docker compose exec php php artisan tinker
>>> DB::connection()->getPdo();
```

## 十一、更新升级

```bash
# 1. 备份数据
docker compose exec mysql mysqldump -u dujiaoka -p dujiaoka > backup.sql

# 2. 停止容器
docker compose down

# 3. 更新代码
git pull

# 4. 重新构建
docker compose up -d --build

# 5. 更新依赖
docker compose exec php composer install

# 6. 清理缓存
docker compose exec php php artisan cache:clear
```

## 优缺点

### 优点
- ✅ 环境隔离，不污染主机
- ✅ 快速部署，一键启动
- ✅ 易于迁移和扩展
- ✅ 版本控制，回滚方便

### 缺点
- ❌ 需要学习 Docker
- ❌ 资源占用稍高
- ❌ 网络配置复杂

## 下一步

- 查看 [基础配置](config-basic.md) 详细配置系统
- 查看 [常见问题](faq.md) 解决常见问题
- 查看 [Docker 最佳实践](https://docs.docker.com/develop/dev-best-practices/)

---

**需要帮助？**
- Telegram: https://t.me/luoyanglang
- GitHub Issues: https://github.com/luoyanglang/dujiaoka/issues
