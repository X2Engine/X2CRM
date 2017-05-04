CREATE TABLE `yiichat_post` (
  `id` CHAR(40),
  `chat_id` CHAR(40) NULL ,
  `post_identity` CHAR(40) NULL ,
  `owner` CHAR(20) NULL ,
  `created` BIGINT(30) NULL ,
  `text` BLOB NULL ,
  `data` BLOB NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `yiichat_chat_id` (`chat_id` ASC),  
  INDEX `yiichat_chat_id_identity` (`chat_id` ASC, `post_identity` ASC) 
)ENGINE = InnoDB;
