CREATE TABLE wcf1_gmap (
	mapID int(10) UNSIGNED NOT NULL auto_increment,
	userID int(10) UNSIGNED NOT NULL DEFAULT 0,
	pt point NOT NULL,
	mapTitle varchar(255) NOT NULL,
	mapInfo TEXT NOT NULL,
	mapInfoCache TEXT NOT NULL,
	PRIMARY KEY  (mapID),
	SPATIAL KEY pt (pt)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE wcf1_user_position (
	userID int(10) UNSIGNED NOT NULL,
	pt point NOT NULL,
	PRIMARY KEY  (userID),
	SPATIAL KEY pt (pt)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

