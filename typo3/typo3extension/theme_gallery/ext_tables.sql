#
# Table structure for table 'tt_content'
#

CREATE TABLE pages (
	tx_theme_gallery_theme_name varchar(255) DEFAULT NULL,
        tx_theme_gallery_theme_style text DEFAULT ''
);
CREATE TABLE tt_content (
	tx_theme_gallery_slide tinyint(4) unsigned DEFAULT '0' NOT NULL
);