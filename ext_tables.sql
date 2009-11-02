#
# Table structure for table 'tt_news'
#
CREATE TABLE tt_news (
	tx_newscalendar_state tinyint(1) unsigned DEFAULT '0' NOT NULL
	tx_newscalendar_calendardate int(11) unsigned DEFAULT '0' NOT NULL
	tx_newscalendar_calendardate_end int(11) unsigned DEFAULT '0' NOT NULL
);