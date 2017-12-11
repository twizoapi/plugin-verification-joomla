

CREATE TABLE IF NOT EXISTS `#__twizodata_users`
(
  id            INT          NOT NULL,
  trustedDevice VARCHAR(512) NULL,
  CONSTRAINT twizodata_users_users_id_fk
  FOREIGN KEY (id) REFERENCES jos_users (id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
);

CREATE INDEX twizodata_users_users_id_fk
  ON `#__twizodata_users` (userId);