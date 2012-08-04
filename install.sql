CREATE TABLE IF NOT EXISTS `api_keys` (
  `api_key_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `api_key` bigint(20) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`api_key_id`),
  UNIQUE KEY `api_key` (`api_key`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;


INSERT INTO `api_keys` (`api_key_id`, `api_key`, `active`) VALUES
(1, 321, 1),
(2, 321222, 0);

CREATE TABLE IF NOT EXISTS `content_types` (
  `content_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `api_key_id` bigint(20) NOT NULL,
  PRIMARY KEY (`content_type_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

INSERT INTO `content_types` (`content_type_id`, `name`, `api_key_id`) VALUES
(1, 'blue-recluse', 1);

CREATE TABLE IF NOT EXISTS `content_type_fields` (
  `content_type_field_id` int(11) NOT NULL AUTO_INCREMENT,
  `content_type_id` int(11) NOT NULL,
  `field` longtext NOT NULL,
  PRIMARY KEY (`content_type_field_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

INSERT INTO `content_type_fields` (`content_type_field_id`, `content_type_id`, `field`) VALUES
(1, 1, 'saltiness11'),
(2, 1, 'epicness'),
(3, 1, 'extra wrapping');

CREATE TABLE IF NOT EXISTS `content_type_items` (
  `content_type_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `content_type_id` int(11) NOT NULL,
  `value` longtext NOT NULL,
  PRIMARY KEY (`content_type_item_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

INSERT INTO `content_type_items` (`content_type_item_id`, `content_type_id`, `value`) VALUES
(5, 1, '{"1":"haha5111","2":"yup6","3":"gogogo7"}'),
(4, 1, '{"1":"a","2":"b","3":"c"}'),
(3, 1, '{"1":"yup","2":"uhhuh","3":"al;kasdkl;jasdjkl;asdfjkl;asdfjkl;asdfjkl;a"}');
