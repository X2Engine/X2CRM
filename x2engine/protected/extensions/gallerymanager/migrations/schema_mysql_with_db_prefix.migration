<?php

class m130123_200915_gallery_tables extends CDbMigration
{
    public function up()
    {
        //return true;
        $this->execute(<<<SQL
CREATE TABLE IF NOT EXISTS `{{gallery}}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `versions_data` text NOT NULL,
  `name` tinyint(1) NOT NULL DEFAULT '1',
  `description` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{{gallery_photo}}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gallery_id` int(11) NOT NULL,
  `rank` int(11) NOT NULL DEFAULT '0',
  `name` varchar(512) NOT NULL,
  `description` text NOT NULL,
  `file_name` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_gallery_photo_gallery1_idx` (`gallery_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

ALTER TABLE `{{gallery_photo}}`
  ADD CONSTRAINT `fk_{{gallery_photo}}_{{gallery}}1` FOREIGN KEY (`gallery_id`) REFERENCES `{{gallery}}` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
SQL
        );
    }

    public function down()
    {
        $this->execute(<<<SQL
DROP TABLE IF EXISTS `{{gallery_photo}}`;
DROP TABLE IF EXISTS `{{gallery}}`;
SQL
        );
        return true;
    }
}