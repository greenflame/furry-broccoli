CREATE TABLE `Document` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(128) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=InnoDB AUTO_INCREMENT=411 DEFAULT CHARSET=utf8;

CREATE TABLE `Entry` (
  `document_id` int(11) unsigned NOT NULL,
  `term` varchar(32) NOT NULL DEFAULT '',
  `count` int(11) NOT NULL,
  PRIMARY KEY (`document_id`,`term`),
  KEY `term` (`term`),
  CONSTRAINT `entry_document_fk` FOREIGN KEY (`document_id`) REFERENCES `Document` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
