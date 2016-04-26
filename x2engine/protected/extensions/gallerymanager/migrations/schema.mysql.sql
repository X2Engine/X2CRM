
-- -----------------------------------------------------
-- Table `gallery`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `x2_gallery` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `versions_data` TEXT NOT NULL ,
  `name` TINYINT(1) NOT NULL DEFAULT 1 ,
  `description` TINYINT(1) NOT NULL DEFAULT 1 ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `gallery_photo`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `x2_gallery_photo` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `gallery_id` INT NOT NULL ,
  `rank` INT NOT NULL DEFAULT 0 ,
  `name` VARCHAR(512) NOT NULL DEFAULT '',
  `description` TEXT NULL,
  `file_name` VARCHAR(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`) ,
  INDEX `fk_gallery_photo_gallery1` (`gallery_id` ASC) ,
  CONSTRAINT `fk_gallery_photo_gallery1`
    FOREIGN KEY (`gallery_id` )
    REFERENCES `gallery` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

