-- ============================================================
-- Product Database Setup
-- Run this script in your MySQL client before first use.
-- ============================================================

CREATE DATABASE IF NOT EXISTS `product_database`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `product_database`;

CREATE TABLE IF NOT EXISTS `products` (
  `id`                INT UNSIGNED     AUTO_INCREMENT PRIMARY KEY,
  `name`              VARCHAR(255)     DEFAULT NULL COMMENT '����',
  `tqb_code`          VARCHAR(100)     DEFAULT NULL COMMENT 'TQB����',
  `oem_number`        VARCHAR(255)     DEFAULT NULL COMMENT 'OEM����',
  `car_series`        VARCHAR(100)     DEFAULT NULL COMMENT '��ϵ',
  `car_model`         VARCHAR(255)     DEFAULT NULL COMMENT '����',
  `universal_model`   VARCHAR(255)     DEFAULT NULL COMMENT 'ͨ�ó���',
  `production_code`   VARCHAR(100)     DEFAULT NULL COMMENT '������',
  `no_stock_purchase` VARCHAR(50)      DEFAULT NULL COMMENT '�޿����ɹ�',
  `trade_car_series`  VARCHAR(100)     DEFAULT NULL COMMENT '��ó��ϵ',
  `trade_car_model`   VARCHAR(255)     DEFAULT NULL COMMENT '��ó����',
  `trade_universal`   VARCHAR(255)     DEFAULT NULL COMMENT '��óͨ�ó���',
  `bca`               VARCHAR(100)     DEFAULT NULL COMMENT 'BCA',
  `skf`               VARCHAR(100)     DEFAULT NULL COMMENT 'SKF',
  `snr`               VARCHAR(100)     DEFAULT NULL COMMENT 'SNR',
  `timken`            VARCHAR(100)     DEFAULT NULL COMMENT 'TIMKEN',
  `nsk`               VARCHAR(100)     DEFAULT NULL COMMENT 'NSK',
  `ntn`               VARCHAR(100)     DEFAULT NULL COMMENT 'NTN',
  `koyo`              VARCHAR(100)     DEFAULT NULL COMMENT 'KOYO',
  `cost`              VARCHAR(50)      DEFAULT NULL COMMENT '�ɱ�',
  `spline_teeth`      VARCHAR(100)     DEFAULT NULL COMMENT '����/�ż�/��Ȧ����',
  `dimensions`        VARCHAR(100)     DEFAULT NULL COMMENT '�ߴ�',
  `weight`            VARCHAR(50)      DEFAULT NULL COMMENT '����',
  `inner_box_size`    VARCHAR(100)     DEFAULT NULL COMMENT '�ںгߴ�',
  `original_category` VARCHAR(100)     DEFAULT NULL COMMENT 'ԭ������',
  `stock_status`      VARCHAR(50)      DEFAULT NULL COMMENT '���״̬',
  `in_system`         VARCHAR(20)      DEFAULT NULL COMMENT '�Ƿ���¼��ϵͳ',
  `system_code`       VARCHAR(100)     DEFAULT NULL COMMENT 'ϵͳ��������',
  `stock_qty`         VARCHAR(20)      DEFAULT NULL COMMENT '�������',
  `stock_max`         VARCHAR(20)      DEFAULT NULL COMMENT '�������',
  `stock_min`         VARCHAR(20)      DEFAULT NULL COMMENT '�������',
  `supplier1`         VARCHAR(100)     DEFAULT NULL COMMENT '��ѡ��Ӧ��',
  `supplier1_price`   VARCHAR(50)      DEFAULT NULL COMMENT '��ѡ�ɹ���',
  `supplier2`         VARCHAR(100)     DEFAULT NULL COMMENT '���ù�Ӧ��1',
  `supplier2_price`   VARCHAR(50)      DEFAULT NULL COMMENT '���òɹ���1',
  `supplier3`         VARCHAR(100)     DEFAULT NULL COMMENT '���ù�Ӧ��2',
  `supplier3_price`   VARCHAR(50)      DEFAULT NULL COMMENT '���òɹ���2',
  `supplier4`         VARCHAR(100)     DEFAULT NULL COMMENT '���ù�Ӧ��3',
  `supplier4_price`   VARCHAR(50)      DEFAULT NULL COMMENT '���òɹ���3',
  `warehouse_a`       VARCHAR(50)      DEFAULT NULL COMMENT 'A�ֿɳ��ж�',
  `created_at`        TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        TIMESTAMP        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX `idx_tqb_code`    (`tqb_code`),
  INDEX `idx_name`        (`name`),
  INDEX `idx_car_series`  (`car_series`),
  INDEX `idx_stock_status`(`stock_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
