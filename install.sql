DROP TABLE IF EXISTS wcf1_gmap;
CREATE TABLE wcf1_gmap (
	mapID int(11) UNSIGNED NOT NULL auto_increment,
	userID int(11) UNSIGNED NOT NULL DEFAULT 0,
	pt point NOT NULL,
	mapTitle varchar(255) NOT NULL,
	mapInfo TEXT NOT NULL,
	mapInfoCache TEXT NOT NULL,
	PRIMARY KEY  (mapID),
	SPATIAL KEY pt (pt)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

