ALTER TABLE `groups`
    ADD COLUMN `join_type` ENUM('open','invite','code') NOT NULL DEFAULT 'open' AFTER `name`,
    ADD COLUMN `invite_code` VARCHAR(64) NULL AFTER `join_type`;
