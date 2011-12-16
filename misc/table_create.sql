create table channel (
  id INTEGER NOT NULL auto_increment,

  name varchar(255),
  view tinyint(1) NOT NULL DEFAULT '1',

  created_on DATETIME,
  updated_on DATETIME,
  readed_on DATETIME,

  unique key (name),
  primary key (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

create table nick (
  id INTEGER NOT NULL auto_increment,

  name varchar(255),

  created_on DATETIME,
  updated_on DATETIME,

  unique key (name),
  primary key (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

create table log (
  id INTEGER auto_increment,

  channel_id INTEGER,
  nick_id INTEGER,
  log text,
  is_notice tinyint(4) DEFAULT NULL,

  created_on datetime,
  updated_on datetime,

  foreign key (channel_id) references channel (id),
  foreign key (nick_id) references nick (id),
  primary key (id),
  KEY `nick_id` (`nick_id`),
  KEY `channel_id_and_created_on` (`channel_id`,`created_on`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE priv (
  id INTEGER  NOT NULL AUTO_INCREMENT,
  nick_id INTEGER  DEFAULT NULL,
  msg text,
  is_notice tinyint(4) DEFAULT NULL,
  is_me tinyint(4) DEFAULT NULL,
  created_on datetime DEFAULT NULL,
  updated_on datetime DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
