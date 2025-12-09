-- ===================================================================
-- 安全功能升级脚本
-- 版本: 2.0.5
-- 日期: 2024-12-07
-- 说明: 添加安全日志功能和限流配置
-- ===================================================================

SET NAMES utf8mb4;

-- 创建安全日志表
CREATE TABLE IF NOT EXISTS `security_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '日志类型：payment_request, suspicious_request',
  `ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'IP地址',
  `url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '请求URL',
  `method` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '请求方法',
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'User Agent',
  `params` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '请求参数',
  `order_sn` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '订单号',
  `reason` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '可疑原因',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ip` (`ip`),
  KEY `idx_order_sn` (`order_sn`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='安全日志表';

-- 添加后台菜单
INSERT INTO `admin_menu` (`parent_id`, `order`, `title`, `icon`, `uri`, `extension`, `show`, `created_at`, `updated_at`)
SELECT 19, 23, '安全日志', 'fa-shield', '/security-log', '', 1, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `admin_menu` WHERE `uri` = '/security-log'
);

-- 完成提示
SELECT '安全功能升级完成！' AS message;
SELECT '请在后台【配置】->【系统设置】->【安全设置】中配置限流参数' AS tip;
