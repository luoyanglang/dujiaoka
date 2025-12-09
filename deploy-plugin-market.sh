#!/bin/bash

echo "========================================="
echo "独角卡数插件市场部署脚本"
echo "========================================="

# 检查是否在项目根目录
if [ ! -f "artisan" ]; then
    echo "错误：请在项目根目录运行此脚本"
    exit 1
fi

echo ""
echo "步骤1：运行数据库迁移..."
php artisan migrate --path=database/migrations/2025_12_07_000001_create_plugins_tables.php

if [ $? -ne 0 ]; then
    echo "错误：数据库迁移失败"
    exit 1
fi

echo ""
echo "步骤2：创建插件目录..."
mkdir -p public/plugins
mkdir -p storage/plugins
chmod -R 755 public/plugins
chmod -R 755 storage/plugins

echo ""
echo "步骤3：清除缓存..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo ""
echo "步骤4：优化..."
php artisan config:cache
php artisan route:cache

echo ""
echo "========================================="
echo "部署完成！"
echo "========================================="
echo ""
echo "下一步："
echo "1. 配置 .env 文件："
echo "   LICENSE_API_URL=https://your-license-api.com"
echo "   LICENSE_API_SECRET=your-secret-key"
echo ""
echo "2. 访问后台同步插件列表："
echo "   后台 -> 插件管理 -> 同步插件列表"
echo ""
echo "3. 访问前台插件市场："
echo "   http://your-domain.com/plugins"
echo ""
