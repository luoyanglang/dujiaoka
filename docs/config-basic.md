# 基础配置指南

> 安装完成后的基本配置说明

## 登录后台

访问：`http://你的域名/admin`

```
默认账号: admin
默认密码: 安装时设置的密码
```

⚠️ **首次登录后请立即修改密码！**

## 一、系统设置

路径：**配置 → 系统设置**

### 基础设置

#### 网站信息
```
网站标题: 你的网站名称
网站Logo: 上传Logo图片（建议尺寸：200x50px）
文字Logo: 纯文本Logo（如不上传图片）
网站关键词: SEO关键词，逗号分隔
网站描述: SEO描述，简短介绍
```

#### 模板设置
```
前端模板: 
  - unicorn（官方默认）
  - luna（简洁风格）
  - hyper（现代风格）

语言设置:
  - zh_CN（简体中文）
  - zh_TW（繁体中文）
  - en（英文）
```

#### 订单设置
```
订单过期时间: 5分钟（默认）
  - 建议：3-10分钟
  - 过短：用户来不及支付
  - 过长：占用库存时间长

管理员邮箱: 接收订单通知的邮箱
```

#### 安全设置
```
开启防红跳转: 关闭（默认）
  - 开启后微信/QQ打开会跳转浏览器

开启图形验证码: 关闭（默认）
  - 开启后下单需要验证码

开启查询密码: 关闭（默认）
  - 开启后查询订单需要密码

开启谷歌翻译: 关闭（默认）
  - 开启后显示语言切换按钮
```

#### 公告设置
```
首页公告: 支持HTML
  示例：
  <p>欢迎光临！</p>
  <p>购买前请仔细阅读商品说明</p>

页脚信息: 支持HTML
  示例：
  <p>© 2024 你的网站. All rights reserved.</p>
  <p><a href="/terms">服务条款</a> | <a href="/privacy">隐私政策</a></p>
```

### 订单推送设置

#### Server酱推送
```
开启Server酱: 关闭（默认）
Server酱Token: 从 https://sct.ftqq.com/ 获取

推送内容：新订单通知
```

#### Telegram推送
```
开启Telegram推送: 关闭（默认）
Bot Token: 从 @BotFather 获取
User ID: 从 @userinfobot 获取

推送内容：新订单通知
```

#### Bark推送（iOS）
```
开启Bark推送: 关闭（默认）
Bark服务器: https://api.day.app
Bark Token: 从Bark App获取

推送内容：新订单通知
```

#### 企业微信推送
```
开启企业微信推送: 关闭（默认）
Webhook地址: 从企业微信群机器人获取

推送内容：新订单通知
```

### 邮件设置

#### SMTP配置
```
驱动: smtp（默认）
SMTP服务器: 
  - QQ邮箱: smtp.qq.com
  - 163邮箱: smtp.163.com
  - Gmail: smtp.gmail.com

SMTP端口:
  - SSL: 465
  - TLS: 587

SMTP用户名: 你的邮箱地址
SMTP密码: 
  - QQ/163: 授权码（不是邮箱密码！）
  - Gmail: 应用专用密码

加密方式: SSL 或 TLS

发件人邮箱: 同SMTP用户名
发件人名称: 你的网站名称
```

#### 获取授权码

**QQ邮箱**：
1. 登录QQ邮箱
2. 设置 → 账户
3. POP3/IMAP/SMTP/Exchange/CardDAV/CalDAV服务
4. 开启SMTP服务
5. 生成授权码

**163邮箱**：
1. 登录163邮箱
2. 设置 → POP3/SMTP/IMAP
3. 开启SMTP服务
4. 设置客户端授权密码

#### 测试邮件

配置完成后：
1. 配置 → Email Test
2. 输入测试邮箱
3. 发送测试邮件
4. 检查是否收到

### 极验设置

```
极验ID: 从 https://www.geetest.com/ 获取
极验Key: 从极验后台获取
开启极验: 关闭（默认）

作用：防止机器人刷单
```

### 安全设置 🔒

路径：**配置 → 系统设置 → 安全设置**

```
订单查询限流: 10次/分钟（默认）
  - 防止订单号枚举攻击
  - 建议：5-20次

邮箱查询限流: 10次/分钟（默认）
  - 防止邮箱枚举攻击
  - 建议：5-20次

浏览器查询限流: 20次/分钟（默认）
  - Cookie查询限制
  - 建议：10-30次

安全日志保留: 30天（默认）
  - 自动清理过期日志
  - 建议：7-90天
```

## 二、邮件模板配置

路径：**配置 → 邮件模板配置**

### 默认模板

系统内置3个邮件模板：

1. **卡密发送邮件** - 自动发货后发送
2. **待处理订单邮件** - 人工处理订单
3. **订单完成邮件** - 订单处理完成

### 模板变量

可用变量：
```
{webname} - 网站名称
{weburl} - 网站地址
{order_id} - 订单号
{ord_title} - 商品标题
{ord_price} - 订单金额
{ord_info} - 卡密信息
{product_name} - 商品名称
{buy_amount} - 购买数量
{created_at} - 创建时间
```

### 自定义模板

点击"编辑"修改模板：

```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{webname}</title>
</head>
<body>
    <h1>订单详情</h1>
    <p>订单号：{order_id}</p>
    <p>商品：{product_name}</p>
    <p>数量：{buy_amount}</p>
    <p>金额：{ord_price} 元</p>
    <p>卡密信息：</p>
    <pre>{ord_info}</pre>
    <p>感谢您的购买！</p>
</body>
</html>
```

## 三、支付配置

路径：**配置 → 支付配置**

详见 [支付配置指南](config-payment.md)

## 四、修改后台路径

### 方法一：修改 .env 文件

```env
ADMIN_ROUTE_PREFIX=/你的自定义路径
```

例如：
```env
ADMIN_ROUTE_PREFIX=/myadmin
```

访问地址变为：`http://你的域名/myadmin`

### 方法二：修改配置文件

编辑 `config/admin.php`：

```php
'route' => [
    'prefix' => env('ADMIN_ROUTE_PREFIX', 'admin'),
],
```

### 清理缓存

```bash
php artisan config:clear
php artisan cache:clear
```

## 五、修改管理员密码

### 方法一：后台修改

1. 登录后台
2. 右上角头像 → 设置
3. 修改密码

### 方法二：命令行修改

```bash
php artisan tinker

$user = App\User::find(1);
$user->password = bcrypt('新密码');
$user->save();
exit
```

## 六、关闭调试模式

⚠️ **生产环境必须关闭！**

编辑 `.env`：

```env
APP_ENV=production
APP_DEBUG=false
```

清理缓存：
```bash
php artisan config:clear
php artisan cache:clear
```

## 七、配置检查清单

安装完成后请检查：

- [ ] 修改管理员密码
- [ ] 配置邮件发送
- [ ] 配置至少一个支付方式
- [ ] 设置网站标题和Logo
- [ ] 配置订单过期时间
- [ ] 配置安全限流
- [ ] 修改后台路径（推荐）
- [ ] 关闭调试模式
- [ ] 测试邮件发送
- [ ] 测试支付回调
- [ ] 测试订单流程

## 八、常见问题

### Q1: 邮件发送失败？

检查：
1. SMTP配置是否正确
2. 是否使用授权码（不是邮箱密码）
3. 端口和加密方式是否匹配
4. 防火墙是否拦截

### Q2: 修改配置不生效？

清理缓存：
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Q3: 忘记管理员密码？

使用命令行重置：
```bash
php artisan tinker
$user = App\User::find(1);
$user->password = bcrypt('新密码');
$user->save();
```

### Q4: 如何备份配置？

导出数据库中的 `admin_settings` 表：
```bash
mysqldump -u root -p dujiaoka admin_settings > settings_backup.sql
```

## 下一步

- 查看 [常见问题](faq.md) 解决常见问题
- 查看 [安全升级](upgrade-security.md) 了解安全功能

---

**需要帮助？**
- 查看 [常见问题](faq.md)
- Telegram: https://t.me/luoyanglang
- GitHub Issues: https://github.com/luoyanglang/dujiaoka/issues
