SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+06:00";
SET NAMES utf8mb4;

SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `takalagbe`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `takalagbe`;

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `userid`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `phone`         VARCHAR(11)  NOT NULL,
  `firstname`     VARCHAR(50)  NOT NULL,
  `lastname`      VARCHAR(50)  NOT NULL,
  `nid`           VARCHAR(18)  NOT NULL,
  `email`         VARCHAR(100) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `pin_hash`      VARCHAR(255) NOT NULL,
  `dob`           DATE         NOT NULL,
  `gender`        ENUM('Male','Female','Others') NOT NULL DEFAULT 'Others',
  `avatar_color`  VARCHAR(7)   NOT NULL DEFAULT '#40A2E3',
  `status`        ENUM('active','frozen') NOT NULL DEFAULT 'active',
  `daily_limit`   DECIMAL(12,2) NOT NULL DEFAULT 25000.00,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`userid`),
  UNIQUE KEY `uq_phone` (`phone`),
  UNIQUE KEY `uq_email` (`email`),
  UNIQUE KEY `uq_nid`   (`nid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `account`;
CREATE TABLE `account` (
  `phone`      VARCHAR(11)   NOT NULL,
  `balance`    DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `updated_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`phone`),
  CONSTRAINT `fk_account_user` FOREIGN KEY (`phone`)
    REFERENCES `user`(`phone`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `chk_balance_non_negative` CHECK (`balance` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `trx_id`           VARCHAR(14)  NOT NULL,
  `transaction_from` VARCHAR(120) NOT NULL,
  `transaction_to`   VARCHAR(120) NOT NULL,
  `transaction_type` VARCHAR(24)  NOT NULL,
  `category`         VARCHAR(24)  NOT NULL DEFAULT 'Other',
  `amount`           DECIMAL(12,2) NOT NULL,
  `fee`              DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `cashback`         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `sender_balance_after`   DECIMAL(12,2) DEFAULT NULL,
  `receiver_balance_after` DECIMAL(12,2) DEFAULT NULL,
  `note`             VARCHAR(120) DEFAULT NULL,
  `status`           ENUM('success','failed','reversed') NOT NULL DEFAULT 'success',
  `transaction_time` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_trx_id` (`trx_id`),
  KEY `idx_from_time` (`transaction_from`, `transaction_time`),
  KEY `idx_to_time`   (`transaction_to`,   `transaction_time`),
  KEY `idx_type`      (`transaction_type`),
  KEY `idx_category`  (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `favourites`;
CREATE TABLE `favourites` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `owner_phone` VARCHAR(11) NOT NULL,
  `saved_phone` VARCHAR(11) NOT NULL,
  `label`       VARCHAR(120) DEFAULT NULL,
  `created_at`  TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_fav` (`owner_phone`,`saved_phone`),
  CONSTRAINT `fk_fav_owner` FOREIGN KEY (`owner_phone`)
    REFERENCES `user`(`phone`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `savings_goals`;
CREATE TABLE `savings_goals` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `phone`          VARCHAR(11)  NOT NULL,
  `title`          VARCHAR(60)  NOT NULL,
  `target_amount`  DECIMAL(12,2) NOT NULL,
  `saved_amount`   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `deadline`       DATE DEFAULT NULL,
  `status`         ENUM('active','completed','cancelled') NOT NULL DEFAULT 'active',
  `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_goal_phone` (`phone`),
  CONSTRAINT `fk_goal_user` FOREIGN KEY (`phone`)
    REFERENCES `user`(`phone`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `money_requests`;
CREATE TABLE `money_requests` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `requester_phone` VARCHAR(11) NOT NULL,
  `payer_phone`     VARCHAR(11) NOT NULL,
  `amount`          DECIMAL(12,2) NOT NULL,
  `note`            VARCHAR(120) DEFAULT NULL,
  `split_group`     VARCHAR(20)  DEFAULT NULL,
  `status`          ENUM('pending','paid','declined') NOT NULL DEFAULT 'pending',
  `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_payer`     (`payer_phone`,`status`),
  KEY `idx_requester` (`requester_phone`,`status`),
  CONSTRAINT `fk_req_requester` FOREIGN KEY (`requester_phone`)
    REFERENCES `user`(`phone`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_req_payer` FOREIGN KEY (`payer_phone`)
    REFERENCES `user`(`phone`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_phone`     VARCHAR(11)  NOT NULL,
  `title`          VARCHAR(80)  NOT NULL,
  `body`           VARCHAR(300) NOT NULL,
  `icon`           VARCHAR(16)  NOT NULL DEFAULT 'bell',
  `amount`         DECIMAL(12,2) DEFAULT NULL,
  `read_status`    TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notif_user` (`user_phone`,`read_status`,`created_at`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_phone`)
    REFERENCES `user`(`phone`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kind`       ENUM('login','admin','pin') NOT NULL DEFAULT 'login',
  `identifier` VARCHAR(64) NOT NULL,
  `ip`         VARCHAR(64) NOT NULL,
  `successful` TINYINT(1)  NOT NULL DEFAULT 0,
  `attempted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_attempt` (`kind`,`identifier`,`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `audit_log`;
CREATE TABLE `audit_log` (
  `id`        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `actor`     VARCHAR(64) NOT NULL,
  `action`    VARCHAR(60) NOT NULL,
  `detail`    VARCHAR(255) DEFAULT NULL,
  `ip`        VARCHAR(64)  DEFAULT NULL,
  `created_at` TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_actor_time` (`actor`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`      VARCHAR(40)  NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admin_user` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `support_messages`;
CREATE TABLE `support_messages` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_name`    VARCHAR(255) NOT NULL,
  `user_email`   VARCHAR(100) NOT NULL,
  `user_phone`   VARCHAR(20)  DEFAULT NULL,
  `message`      TEXT         NOT NULL,
  `issue_solved` TINYINT(1)   NOT NULL DEFAULT 0,
  `timestamp`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_solved` (`issue_solved`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `organizations`;
CREATE TABLE `organizations` (
  `id`                    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organization_id`       VARCHAR(20)  NOT NULL,
  `organization_name`     VARCHAR(255) NOT NULL,
  `total_received_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `billtype`              VARCHAR(50)  NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_org` (`organization_id`),
  KEY `idx_billtype` (`billtype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `institutes`;
CREATE TABLE `institutes` (
  `id`                    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institute_id`          VARCHAR(20)  NOT NULL,
  `institute_name`        VARCHAR(255) NOT NULL,
  `total_received_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_inst` (`institute_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `bill_payments`;
CREATE TABLE `bill_payments` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `payer_phone`       VARCHAR(11)  NOT NULL,
  `billtype`          VARCHAR(50)  NOT NULL,
  `organization_id`   VARCHAR(20)  NOT NULL,
  `organization_name` VARCHAR(255) NOT NULL,
  `consumer_no`       VARCHAR(40)  NOT NULL,
  `amount`            DECIMAL(12,2) NOT NULL,
  `month`             VARCHAR(20)  NOT NULL,
  `trx_id`            VARCHAR(14)  DEFAULT NULL,
  `payment_date`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_bill_payer` (`payer_phone`,`payment_date`),
  KEY `idx_bill_type`  (`billtype`),
  KEY `idx_bill_org`   (`organization_id`),
  UNIQUE KEY `uq_bill_month` (`billtype`,`organization_id`,`consumer_no`,`month`),
  CONSTRAINT `fk_bill_payer` FOREIGN KEY (`payer_phone`)
    REFERENCES `user`(`phone`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_bill_org` FOREIGN KEY (`organization_id`)
    REFERENCES `organizations`(`organization_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `education_fee`;
CREATE TABLE `education_fee` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `payer_phone`    VARCHAR(11)  NOT NULL,
  `institute_id`   VARCHAR(20)  NOT NULL,
  `institute_name` VARCHAR(255) NOT NULL,
  `student_id`     VARCHAR(30)  NOT NULL,
  `student_name`   VARCHAR(255) NOT NULL,
  `semester`       VARCHAR(50)  NOT NULL,
  `amount`         DECIMAL(12,2) NOT NULL,
  `trx_id`         VARCHAR(14)  DEFAULT NULL,
  `timestamp`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_edu_payer` (`payer_phone`),
  KEY `idx_edu_inst`  (`institute_id`),
  UNIQUE KEY `uq_edu_semester` (`institute_id`,`student_id`,`semester`),
  CONSTRAINT `fk_edu_payer` FOREIGN KEY (`payer_phone`)
    REFERENCES `user`(`phone`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_edu_inst` FOREIGN KEY (`institute_id`)
    REFERENCES `institutes`(`institute_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `mobile_recharge`;
CREATE TABLE `mobile_recharge` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `payer_phone`   VARCHAR(11) NOT NULL,
  `target_phone`  VARCHAR(11) NOT NULL,
  `operator`      VARCHAR(20) NOT NULL,
  `connection`    ENUM('Prepaid','Postpaid') NOT NULL DEFAULT 'Prepaid',
  `amount`        DECIMAL(12,2) NOT NULL,
  `trx_id`        VARCHAR(14) DEFAULT NULL,
  `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_recharge_payer` (`payer_phone`,`created_at`),
  CONSTRAINT `fk_recharge_payer` FOREIGN KEY (`payer_phone`)
    REFERENCES `user`(`phone`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `merchants`;
CREATE TABLE `merchants` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `merchant_id`   VARCHAR(20)  NOT NULL,
  `merchant_name` VARCHAR(120) NOT NULL,
  `category`      VARCHAR(30)  NOT NULL DEFAULT 'Shopping',
  `cashback_pct`  DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `total_received_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_merchant` (`merchant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `agent_details`;
CREATE TABLE `agent_details` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `agent_id`       VARCHAR(40) NOT NULL,
  `phone_number`   VARCHAR(11) NOT NULL,
  `cash_in_amount` DECIMAL(12,2) NOT NULL,
  `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_agent` (`agent_id`),
  CONSTRAINT `chk_float_non_negative` CHECK (`cash_in_amount` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `organizations` (`organization_id`,`organization_name`,`total_received_amount`,`billtype`) VALUES
('111','Titas Gas Limited',0,'gas_bills'),
('112','Karnaphuli Gas Limited',0,'gas_bills'),
('113','Petrobangla Gas Limited',0,'gas_bills'),
('114','Bakhrabad Gas Limited',0,'gas_bills'),
('222','Dhaka WASA',0,'water_bills'),
('223','Chattogram WASA',0,'water_bills'),
('224','Khulna WASA',0,'water_bills'),
('225','Rajshahi WASA',0,'water_bills'),
('333','Bnet',0,'internet_bills'),
('334','X-press Technologies',0,'internet_bills'),
('335','Sam Online',0,'internet_bills'),
('336','Exord Online',0,'internet_bills'),
('444','DPDC',0,'electricity_bills'),
('445','DESCO',0,'electricity_bills'),
('446','Palli Bidyut',0,'electricity_bills'),
('447','BPDB',0,'electricity_bills');

INSERT INTO `institutes` (`institute_id`,`institute_name`,`total_received_amount`) VALUES
('555','BRAC University',0),
('556','BUET',0),
('557','Notre Dame College',0),
('558','National Ideal College',0);

INSERT INTO `merchants` (`merchant_id`,`merchant_name`,`category`,`cashback_pct`) VALUES
('M101','Aarong','Shopping',1.00),
('M102','Shwapno Superstore','Groceries',1.50),
('M103','Pizza Hut Bangladesh','Food',2.00),
('M104','Star Kabab','Food',2.00),
('M105','Daraz Bangladesh','Shopping',0.50),
('M106','Uber Bangladesh','Transport',1.00),
('M107','Lazz Pharma','Health',1.00),
('M108','Rokomari','Books',1.00);

INSERT INTO `agent_details` (`agent_id`,`phone_number`,`cash_in_amount`) VALUES
('AG1001','01711000001',50000.00),
('AG1002','01711000002',50000.00),
('AG1003','01711000003',50000.00);

INSERT INTO `admin` (`username`,`password_hash`) VALUES
('admin','$2y$12$2E8zfqahDXux.q98sbq01.N3lqsGxveByWV6p5YbgLbI5aHFw2/Ba');

DROP TRIGGER IF EXISTS `trg_bill_payment_total`;
CREATE TRIGGER `trg_bill_payment_total`
AFTER INSERT ON `bill_payments`
FOR EACH ROW
  UPDATE `organizations`
     SET `total_received_amount` = `total_received_amount` + NEW.`amount`
   WHERE `organization_id` = NEW.`organization_id`;

DROP TRIGGER IF EXISTS `trg_education_fee_total`;
CREATE TRIGGER `trg_education_fee_total`
AFTER INSERT ON `education_fee`
FOR EACH ROW
  UPDATE `institutes`
     SET `total_received_amount` = `total_received_amount` + NEW.`amount`
   WHERE `institute_id` = NEW.`institute_id`;

SET FOREIGN_KEY_CHECKS = 1;
