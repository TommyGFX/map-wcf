CREATE TABLE wcf1_gmap_personal (
	mapID int(10) UNSIGNED NOT NULL auto_increment,
	userID int(10) UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY  (mapID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE wcf1_gmap_personal_data (
	dataID int(10) UNSIGNED NOT NULL auto_increment,
	collection int(10) UNSIGNED NOT NULL DEFAULT 0,
	mapID int(10) UNSIGNED NOT NULL DEFAULT 0,
	type ENUM('point') NOT NULL DEFAULT 'point',
	text TEXT NOT NULL DEFAULT '',
	pt point NOT NULL,
	PRIMARY KEY  (dataID),
	INDEX  (mapID),
	SPATIAL KEY pt (pt)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE wcf1_gmap_user (
	userID int(10) UNSIGNED NOT NULL,
	pt point NOT NULL,
	PRIMARY KEY  (userID),
	SPATIAL KEY pt (pt)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

