# Fork 说明

## 关于本项目

本项目是基于 [assimon/dujiaoka](https://github.com/assimon/dujiaoka) 的 Fork 版本，在原项目基础上进行了安全增强和功能改进。

## 与原项目的关系

### 原作者信息
- **原项目**: [assimon/dujiaoka](https://github.com/assimon/dujiaoka)
- **原作者**: Assimon
- **原作者邮箱**: ashang@utf8.hk
- **原作者网站**: https://utf8.hk
- **原作者 Telegram**: https://t.me/dujiaoka

### 本项目信息
- **项目地址**: [luoyanglang/dujiaoka](https://github.com/luoyanglang/dujiaoka)
- **维护者**: luoyanglang
- **Telegram**: https://t.me/luoyanglang

## 主要改进

### v2.0.5 安全增强版

1. **安全日志系统**
   - 记录所有支付请求
   - 记录可疑行为
   - 后台可视化管理

2. **动态限流配置**
   - 订单查询限流
   - 邮箱查询限流
   - 浏览器查询限流
   - 后台实时配置

3. **XSS 防护增强**
   - 邮件模板变量转义
   - 订单信息输出转义
   - 输入格式验证

4. **支付网关增强**
   - 请求日志记录
   - 订单号格式验证
   - 可疑请求拦截

5. **Windows 部署支持**
   - 完整的 Windows 部署文档
   - PhpStudy/Laragon 方案
   - 队列和 Redis 解决方案

6. **完整文档体系**
   - 40+ 篇详细文档
   - 中文文档完善
   - 常见问题汇总

## 开源协议

本项目继承原项目的 [MIT 开源协议](https://opensource.org/licenses/MIT)。

### 你可以：
- ✅ 免费使用
- ✅ 修改源代码
- ✅ 商业使用
- ✅ 二次开发
- ✅ 分发副本

### 你需要：
- ✅ 保留原作者版权声明
- ✅ 保留本项目版权声明
- ✅ 说明修改内容

### 你不能：
- ❌ 声称是原创作品
- ❌ 用于违法用途
- ❌ 要求作者承担责任

## 贡献指南

欢迎提交 Pull Request 和 Issue！

### 提交 PR 前请：
1. Fork 本项目
2. 创建特性分支
3. 提交代码并测试
4. 发起 Pull Request

### 提交 Issue 时请：
1. 描述问题现象
2. 提供复现步骤
3. 附上错误日志
4. 说明环境信息

## 免责声明

1. 本项目仅供学习交流使用
2. 不可用于违反法律法规的用途
3. 使用者需自行承担法律责任
4. 维护者不参与任何用户的业务运营
5. 不提供收费技术支持服务

## 致谢

感谢原作者 Assimon 的开源贡献，让我们能够在此基础上继续改进和完善。

同时感谢所有为本项目提供建议和帮助的朋友们！

## 联系方式

- **Telegram**: https://t.me/luoyanglang
- **GitHub Issues**: https://github.com/luoyanglang/dujiaoka/issues
