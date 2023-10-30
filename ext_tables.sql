CREATE TABLE tx_calendarizeexternal_domain_model_calendar (
	title varchar(255) NOT NULL DEFAULT '',
	ics_url varchar(255) NOT NULL DEFAULT '',
	scheduler_interval int(11) DEFAULT '0' NOT NULL,
	note varchar(255) NOT NULL DEFAULT '',
	last_run int(11) NOT NULL DEFAULT '0',
	last_message varchar(255) NOT NULL DEFAULT '',
	error_count int(11) NOT NULL DEFAULT '0',
	md5 varchar(32) DEFAULT '',
	description text,
);
