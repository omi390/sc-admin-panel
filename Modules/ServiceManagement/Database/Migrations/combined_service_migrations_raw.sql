-- Combined raw MySQL for service-related migrations (2026_02_05)
-- Run in order. Ensure `services` and `faqs` tables exist before running.

-- ---------------------------------------------------------------------------
-- 1. service_procedures
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `service_procedures` (
    `id` CHAR(36) NOT NULL,
    `service_id` CHAR(36) NOT NULL,
    `image_url` VARCHAR(500) NULL,
    `title` VARCHAR(191) NULL,
    `description` TEXT NULL,
    `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `service_procedures_service_id_foreign`
        FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
);

-- ---------------------------------------------------------------------------
-- 2. service_notes
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `service_notes` (
    `id` CHAR(36) NOT NULL,
    `service_id` CHAR(36) NOT NULL,
    `title` VARCHAR(191) NULL,
    `description` TEXT NULL,
    `image` VARCHAR(500) NULL,
    `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `service_notes_service_id_foreign`
        FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
);

-- ---------------------------------------------------------------------------
-- 3. service_pros_and_cons
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `service_pros_and_cons` (
    `id` CHAR(36) NOT NULL,
    `service_id` CHAR(36) NOT NULL,
    `title` VARCHAR(191) NULL,
    `prod_or_con` ENUM('pros', 'con') NOT NULL,
    `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `service_pros_and_cons_service_id_foreign`
        FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
);

-- ---------------------------------------------------------------------------
-- 4. service_policies
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `service_policies` (
    `id` CHAR(36) NOT NULL,
    `service_id` CHAR(36) NOT NULL,
    `title` VARCHAR(191) NULL,
    `description` TEXT NULL,
    `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `service_policies_service_id_foreign`
        FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
);

-- ---------------------------------------------------------------------------
-- 5. faqs: add title, description
-- (MySQL 8.0.12+: use IF NOT EXISTS; older MySQL: run the two ALTERs below once)
-- ---------------------------------------------------------------------------
-- MySQL 8.0.12+:
-- ALTER TABLE `faqs`
--     ADD COLUMN IF NOT EXISTS `title` VARCHAR(191) NULL AFTER `service_id`,
--     ADD COLUMN IF NOT EXISTS `description` TEXT NULL AFTER `title`;
ALTER TABLE `faqs` ADD COLUMN `title` VARCHAR(191) NULL AFTER `service_id`;
ALTER TABLE `faqs` ADD COLUMN `description` TEXT NULL AFTER `title`;

-- ---------------------------------------------------------------------------
-- 6. services: add code_text, code_img
-- (If `images` does not exist, change AFTER `images` to e.g. AFTER `thumbnail`)
-- ---------------------------------------------------------------------------
ALTER TABLE `services` ADD COLUMN `code_text` TEXT NULL AFTER `images`;
ALTER TABLE `services` ADD COLUMN `code_img` VARCHAR(500) NULL AFTER `code_text`;
