


-- Drop Facebook configuration columns
ALTER TABLE `%PREFIX%config` DROP COLUMN `fb_on`;
ALTER TABLE `%PREFIX%config` DROP COLUMN `fb_apikey`;
ALTER TABLE `%PREFIX%config` DROP COLUMN `fb_skey`;

