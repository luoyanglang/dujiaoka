<p align="center"><img src="https://i.loli.net/2020/04/07/nAzjDJlX7oc5qEw.png" width="400"></p>

<p align="center">
<a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/badge/license-MIT-blue" alt="license MIT"></a>
<a href="https://github.com/luoyanglang/dujiaoka"><img src="https://img.shields.io/badge/version-v1.0纯净安全版-brightgreen" alt="version v1.0"></a>
<a href="https://www.php.net/releases/7_4_0.php"><img src="https://img.shields.io/badge/PHP-7.4-lightgrey" alt="php74"></a>
<a href="#"><img src="https://img.shields.io/badge/安全等级-⭐⭐⭐⭐⭐-red" alt="security level"></a>
</p>

# **独角卡数纯净安全版 v1.0**

<p align="center">
  <strong>🎯 纯净 · 安全 · 插件化 · 可扩展</strong>
</p>

<sub>基于原版深度重构，移除冗余功能 + 11项安全加固 + 插件化架构 + 完整审计日志</sub>

---

## ✨ v1.0 纯净版特色

### 🎯 纯净化设计
<details>
<summary><strong>点击展开详情</strong></summary>

- ✅ **核心功能保留**：商品管理、订单管理、卡密管理、邮件模板、系统设置
- ✅ **扩展功能移除**：12个支付方式、优惠券系统（改为插件提供）
- ✅ **代码精简**：删除 1400+ 行冗余代码，系统更轻量
- ✅ **按需安装**：通过插件市场按需安装支付方式和功能扩展
- ✅ **降低复杂度**：新手更容易上手，配置更简单

**优势**：
- 初次安装无需配置支付接口
- 界面更简洁，学习成本更低
- 系统更稳定，减少潜在冲突
- 为插件化生态做好准备

</details>

### 🔒 安全加固（23项）
<details>
<summary><strong>点击展开详情</strong></summary>

#### 插件市场安全（7项）
- ✅ **路径遍历防护**：防止恶意插件覆盖系统文件
- ✅ **文件类型白名单**：只允许安全的文件扩展名
- ✅ **SSRF 攻击防护**：下载地址白名单 + 强制 HTTPS
- ✅ **SSL 证书验证**：防止中间人攻击
- ✅ **文件完整性检查**：验证 plugin.json 和 slug 一致性
- ✅ **魔术字节验证**：确保文件类型真实性
- ✅ **文件大小限制**：防止超大文件攻击（50MB）

#### 访问控制安全（4项）
- ✅ **速率限制**：防止暴力破解授权码（5次/10分钟）
- ✅ **错误信息脱敏**：智能错误提示，隐藏系统内部信息
- ✅ **完整审计日志**：记录所有插件操作（安装、卸载、激活）
- ✅ **异常隔离机制**：插件加载失败不影响系统运行

#### 核心安全加固（8项）
- ✅ **会话固定防护**：登录后自动重新生成会话ID
- ✅ **Cookie安全配置**：启用Secure、HttpOnly、SameSite属性
- ✅ **订单号加密生成**：使用`random_bytes()`加密安全随机
- ✅ **插件路径验证**：防止任意文件读取攻击
- ✅ **邮件XSS防护**：HTML净化，只允许安全标签
- ✅ **安全响应头**：X-Frame-Options、CSP、HSTS等7个安全头
- ✅ **本地路径隔离**：严格限制插件文件访问范围
- ✅ **Webhook SSRF防护**：禁止内网IP访问，防止外部服务端请求伪造

#### 输入验证加固（4项）
- ✅ **查询密码验证**：6-32位，仅允许字母数字和安全符号
- ✅ **订单查询限流**：防止暴力查询（10次/分钟）
- ✅ **代理信任配置**：防止IP伪造攻击
- ✅ **机器码加固**：使用SHA256替代MD5
- ✅ **管理员密码强化**：安装时支持随机生成强密码或自定义密码（≥8位）

**安全等级**：⭐⭐⭐⭐⭐ 高

**防护能力**：
- 🛡️ 路径遍历攻击
- 🛡️ SSRF 攻击
- 🛡️ 中间人攻击
- 🛡️ 暴力破解
- 🛡️ 代码注入
- 🛡️ 信息泄露
- 🛡️ 会话劫持
- 🛡️ XSS攻击
- 🛡️ 点击劫持
- 🛡️ IP伪造
- 🛡️ Hash碰撞

**审计能力**：
- 📝 完整操作日志
- 📝 失败尝试记录
- 📝 异常事件告警
- 📝 安全事件追溯

</details>

### 🧩 插件化架构
<details>
<summary><strong>点击展开详情</strong></summary>

#### 插件市场
- ✅ **在线浏览**：浏览所有可用插件（免费 + 付费）
- ✅ **一键安装**：免费插件直接安装，付费插件购买后激活
- ✅ **授权管理**：域名绑定 + 机器码验证
- ✅ **自动更新**：插件版本管理和更新提醒
- ✅ **安全隔离**：插件独立运行，互不影响



**优势**：
- 按需安装，降低系统复杂度
- 独立更新，不影响核心系统
- 第三方开发者可贡献插件
- 商业化插件生态

</details>

### 📚 原有安全增强（v2.0.5）
<details>
<summary><strong>点击展开详情</strong></summary>

- ✅ **安全日志系统**：记录所有敏感操作
- ✅ **动态限流配置**：防止 DDoS 攻击
- ✅ **XSS 防护增强**：输入输出过滤
- ✅ **输入验证强化**：严格的参数验证
- ✅ **支付网关增强**：支付安全加固
- ✅ **完整中文文档**：13篇详细教程
- ✅ **4种部署方案**：宝塔、Docker、Linux、Windows
- ✅ **29个常见问题**：新手友好

</details>

### 🎯 快速开始
- 📖 [快速安装](docs/quick-start.md) - 5分钟快速部署
- 🔒 [安全加固记录](安全加固记录.md) - 安全功能详解
- 🧩 [插件市场说明](插件后台购买集成方案.md) - 插件化架构
- 📚 [文档中心](docs/README.md) - 完整文档

---
开源式站长自动化售货解决方案、高效、稳定、快速！

- 框架来自：[laravel/framework](https://github.com/laravel/laravel).
- 后台管理系统：[laravel-admin](https://laravel-admin.org/).
- 前端ui [bootstrap](https://getbootstrap.com/).

核心贡献者：
- [iLay1678](https://github.com/iLay1678)

模板贡献者：
- [Julyssn](https://github.com/Julyssn) 模板`luna`作者
- [bimoe](https://github.com/bimoe) 模板`hyper`作者

鸣谢以上开源项目及贡献者，排名不分先后.

## 系统优势

采用业界流行的`laravel`框架，安全及稳定性提升。    
支持`自定义前端模板`功能   
支持`国际化多语言包`（需自行翻译）  
代码全部开源，所有扩展包采用composer加载，代码所有内容可溯源！     
长期技术更新支持！
    

## 写在前面
本程序有一定的上手难度（对于小白而言），需要您对linux服务器有基本的认识和操作度   
且本程序不支持虚拟主机
[windows服务器部署](docs/install-windows.md)（不推荐）！  
如果您连宝塔、phpstudy、AppNode等一键可视化服务器面板也未曾使用或听说过，那么我大概率劝您放弃本程序！  
如果您觉得部署有难度，建议仔细阅读（仔细！）宝塔视频安装篇教程，里面有保姆级的安装流程和视频教程！   
认真观看部署教程我可以保证您98%可能性能部署成功！  
勤动手，多思考，善研究！

## 使用交流      
Telegram: [https://t.me/luoyanglang](https://t.me/luoyanglang)    
GitHub: [https://github.com/luoyanglang/dujiaoka](https://github.com/luoyanglang/dujiaoka)

## 🔥推荐服务商

🔥全球稳定过墙及开25端口物理机[👉🏻点我直达找台妹](https://t.me/XMOhost8888)

🔥🔥全球优质CDN服务商[👉🏻点我直达找台妹](https://t.me/XMOhost8888)

🔥🔥🔥高效又稳定，算力更狠劲——服务器特价入手不心疼！[👉点我直达购买](https://t.me/zihaofuwuqi)

## 原作者信息

本项目基于 [assimon/dujiaoka](https://github.com/assimon/dujiaoka) 开发，感谢原作者的开源贡献！

- 原作者 GitHub: [https://github.com/assimon/dujiaoka](https://github.com/assimon/dujiaoka)
- 原作者 Telegram: [https://t.me/dujiaoka](https://t.me/dujiaoka)

## 界面尝鲜
【官方unicorn模板】
![首页.png](https://i.loli.net/2021/09/14/NZIl6s9RXbHwkmA.png)

【luna模板】 
![首页.png](https://i.loli.net/2020/10/24/ElKwJFsQy4a9fZi.png)

【hyper模板】  
![首页.png](https://i.loli.net/2021/01/06/nHCSV5PdJIzT6Gy.png)

## 📚 文档导航

### 快速开始
- [项目介绍](docs/introduction.md) - 了解独角数卡
- [环境要求](docs/requirements.md) - 部署前必读
- [快速安装](docs/quick-start.md) - 5分钟快速部署 ⭐推荐新手
- [常见问题](docs/faq.md) - 高频问题汇总

### 安装部署
- [宝塔面板部署](docs/install-bt.md) - 推荐新手 ⭐
- [Windows 部署](docs/install-windows.md) - 开发测试环境 ⚠️
- [Docker 部署](docs/install-docker.md) - 容器化部署
- [Linux 手动部署](docs/install-linux.md) - 适合有经验用户

### 升级维护
- [安全功能升级](docs/upgrade-security.md) - v2.0.5 安全增强 🔒
- [系统升级指南](docs/maintenance-upgrade.md) - 版本升级
- [数据备份](docs/maintenance-backup.md) - 数据安全

### 更多文档
查看 [完整文档目录](docs/README.md) 获取 40+ 篇详细文档



## 支付接口已集成
- [x] 支付宝当面付
- [x] 支付宝PC支付
- [x] 支付宝手机支付
- [x] [payjs微信扫码](http://payjs.cn).
- [x] [Paysapi(支付宝/微信)](https://www.paysapi.com/).
- [x] 码支付(QQ/支付宝/微信)
- [x] 微信企业扫码支付
- [x] [Paypal支付(默认美元)](https://www.paypal.com)
- [x] V免签支付
- [x] 全网易支付支持(通用彩虹版)
- [x] [stripe](https://stripe.com/)

## 基本环境要求

- (PHP + PHPCLI) version = 7.4
- Nginx version >= 1.16
- MYSQL version >= 5.6
- Redis (高性能缓存服务)
- Supervisor (一个python编写的进程管理服务)
- Composer (PHP包管理器)
- Linux (Win下未测试，建议直接Linux)

## PHP环境要求

星号(*)为必须执行的要求，其他为建议内容

- **\*安装`fileinfo`扩展**
- **\*安装`redis`扩展**
- **\*终端需支持`php-cli`，测试`php -v`(版本必须一致)**
- **\*需要开启的函数：`putenv`，`proc_open`，`pcntl_signal`，`pcntl_alarm`**
- 安装`opcache`扩展

## 后台访问

- 后台路径 `/admin` （可在安装时自定义）
- 管理员账号在安装时设置：
  - **随机生成模式**：系统会生成强密码，安装完成后显示一次（推荐，更安全）
  - **自定义模式**：您可以设置自己的用户名和密码（密码至少8位）

## 免责声明

独角数卡程序是免费开源的产品，仅用于学习交流使用！       
不可用于任何违反`中华人民共和国(含台湾省)`或`使用者所在地区`法律法规的用途。      
因为作者即本人仅完成代码的开发和开源活动`(开源即任何人都可以下载使用)`，从未参与用户的任何运营和盈利活动。    
且不知晓用户后续将`程序源代码`用于何种用途，故用户使用过程中所带来的任何法律责任即由用户自己承担。      


## Thanks

Thanks JetBrains for the free open source license

<a href="https://www.jetbrains.com/?from=gev" target="_blank">
	<img src="https://i.loli.net/2021/02/08/2aejB8rwNmQR7FG.png" width = "260" align=center />
</a>


## License

独角数卡 DJK Inc [MIT license](https://opensource.org/licenses/MIT).
