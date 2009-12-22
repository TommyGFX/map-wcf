CREATE TABLE wcf1_user_position (
	userID int(10) UNSIGNED NOT NULL,
	pt point NOT NULL,
	PRIMARY KEY  (userID),
	SPATIAL KEY pt (pt)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

