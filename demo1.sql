CREATE TABLE `form`
(
	`id`             MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Questo è l''indice della tabella',
	`nome`           VARCHAR(255)          NOT NULL,
	`cognome`        VARCHAR(255)          NOT NULL,
	`email`          VARCHAR(255)          NOT NULL,
	`messaggio`      TEXT                  NOT NULL,
	`data_ricezione` DATETIME              NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
	PRIMARY KEY (`id`),
	KEY `nome` (`nome`),
	KEY `cognome` (`cognome`),
	KEY `email` (`email`),
	KEY `data_ricezione` (`data_ricezione`) USING BTREE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;

CREATE TABLE `users`
(
	`fullname`    VARCHAR(255)          NOT NULL,
	`id`          MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	`usernm`      VARCHAR(255)          NOT NULL,
	`passwd`      VARCHAR(50)           NOT NULL,
	`insert_date` DATETIME              NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
	PRIMARY KEY (`id`),
	UNIQUE KEY `UNIQUE_user` (`usernm`, `passwd`),
	KEY `usernm` (`usernm`),
	KEY `passwd` (`passwd`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;
