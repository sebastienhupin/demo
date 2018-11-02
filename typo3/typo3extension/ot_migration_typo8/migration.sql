
/**
 * Author:  sebastienhupin
 * Created: Dec 15, 2017
 */
/**
    !!!!
    L'extension rx_shariff doit être activée
    !!!!
    L'extension ot_cms doit être activée et le champs tx_opentalent_structure_id structure doit existé dans la table 
    pages.
    !!!
    Ajout de l'uid de la structure pour la page root, sert notament à mettre le répertoire d'upload pour les utilisateurs backends
    /fileadmin/user_upload/<tx_opentalent_structure_id>/...
    ex : pour adminohcluses
    /fileadmin/user_upload/498/...
*/
UPDATE openassos.pages AS op
INNER JOIN openassos.be_users AS obe ON op.uid IN (obe.db_mountpoints)
INNER JOIN adminassos.fe_users AS afe ON afe.username = obe.username
INNER JOIN adminassos.oa_users_assos AS aus ON aus.fe_users_uid_uas = afe.uid
SET op.tx_opentalent_structure_id = aus.oa_assos_uid_uas
WHERE
	op.is_siteroot = 1

;

UPDATE openassos.sys_template AS t
INNER JOIN openassos.pages AS p ON p.uid = t.pid
SET
    t.include_static_file = "EXT:frontend_editing/Configuration/TypoScript,EXT:fluid_styled_content/Configuration/TypoScript/,EXT:fluid_styled_content/Configuration/TypoScript/Styling/,EXT:form/Configuration/TypoScript/,EXT:news/Configuration/TypoScript,EXT:ot_webservice/Configuration/TypoScript,EXT:theme_gallery/Configuration/TypoScript,EXT:piwikintegration/Configuration/TypoScript/",
    t.config = REPLACE(REPLACE(t.config, '.admPanel', '.frontend_editing'), 'VERSION 4.0', 'VERSION 8.0')
WHERE
    p.is_siteroot = 1
AND
    (p.uid <> 32365 AND p.uid <> 95142);


UPDATE openassos.sys_template AS t
INNER JOIN openassos.pages AS p ON p.uid = t.pid
SET
    t.include_static_file = "EXT:fluid_styled_content/Configuration/TypoScript/,EXT:fluid_styled_content/Configuration/TypoScript/Styling/,EXT:form/Configuration/TypoScript/,EXT:news/Configuration/TypoScript,EXT:ot_webservice/Configuration/TypoScript",
    t.config = REPLACE(REPLACE(t.config, '.admPanel', '.frontend_editing'), 'VERSION 4.0', 'VERSION 8.0')
WHERE
    p.is_siteroot = 1
AND
    (p.uid = 95142);

UPDATE openassos.sys_template AS t
INNER JOIN openassos.pages AS p ON p.uid = t.pid
SET
    t.include_static_file = "EXT:fluid_styled_content/Configuration/TypoScript/,EXT:fluid_styled_content/Configuration/TypoScript/Styling/,EXT:ot_webservice/Configuration/TypoScript,EXT:news/Configuration/TypoScript,EXT:theme_gallery/Configuration/TypoScript",
    t.config = REPLACE(REPLACE(t.config, '.admPanel', '.frontend_editing'), 'VERSION 4.0', 'VERSION 8.0')
WHERE
    p.is_siteroot = 1
AND
    (p.uid = 32365);


/**
Action relative au moteur de recherche pour le portail
 */
UPDATE openassos.tt_content SET pi_flexform = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<T3FlexForms>
    <data>
        <sheet index="sDEF">
            <language index="lDEF"></language>
        </sheet>
        <sheet index="General">
            <language index="lDEF">
                <field index="switchableControllerActions">
                    <value index="vDEF">Event-&gt;searchForm</value>
                </field>
            </language>
        </sheet>
    </data>
</T3FlexForms>' WHERE uid IN(115271, 115272) ;

UPDATE openassos.tt_content SET pi_flexform = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<T3FlexForms>
    <data>
        <sheet index="sDEF">
            <language index="lDEF"></language>
        </sheet>
        <sheet index="General">
            <language index="lDEF">
                <field index="switchableControllerActions">
                    <value index="vDEF">Structure-&gt;searchForm</value>
                </field>
            </language>
        </sheet>
    </data>
</T3FlexForms>' WHERE uid IN(115274, 115275, 132917, 132918) ;

/**
Gère les espaces définit AVANT un block content
 */
UPDATE openassos.tt_content SET space_before_class = 'extra-small' WHERE spaceBefore > 0 AND spaceBefore <= 10;
UPDATE openassos.tt_content SET space_before_class = 'small' WHERE spaceBefore > 10 AND spaceBefore <= 20;
UPDATE openassos.tt_content SET space_before_class = 'medium' WHERE spaceBefore > 20 AND spaceBefore <= 30;
UPDATE openassos.tt_content SET space_before_class = 'large' WHERE spaceBefore > 30 AND spaceBefore <= 40;
UPDATE openassos.tt_content SET space_before_class = 'extra-large' WHERE spaceBefore > 40;
/**
Gère les espaces définit APRES un block content
 */
UPDATE openassos.tt_content SET space_after_class = 'extra-small' WHERE spaceAfter > 0 AND spaceAfter <= 10;
UPDATE openassos.tt_content SET space_after_class = 'small' WHERE spaceAfter > 10 AND spaceAfter <= 20;
UPDATE openassos.tt_content SET space_after_class = 'medium' WHERE spaceAfter > 20 AND spaceAfter <= 30;
UPDATE openassos.tt_content SET space_after_class = 'large' WHERE spaceAfter > 30 AND spaceAfter <= 40;
UPDATE openassos.tt_content SET space_after_class = 'extra-large' WHERE spaceAfter > 40;

UPDATE openassos.tt_content SET bodytext = replace(bodytext, 'src="http://', 'src="https://');

UPDATE openassos.tt_content SET bodytext = replace(bodytext, 'uploads/RTEmagicC', '/fileadmin/_migrated/RTE/RTEmagicC');

UPDATE openassos.sys_template
 SET constants = replace(constants, '/opentalent/images/logo-cmfreseau.png', '/fileadmin/theme_gallery/BlueSky/Templates/assets/img/logo-cmf.png');

UPDATE openassos.sys_template
 SET constants = replace(constants, '/opentalent/images/logo-ffecreseau.png', '/fileadmin/theme_gallery/BlueSky/Templates/assets/img/csm_ffec.jpg');

UPDATE openassos.sys_template
 SET constants = replace(constants, '/opentalent/images/logo-yavreseau.png', '/fileadmin/theme_gallery/BlueSky/Templates/assets/img/csm_yav.png');

UPDATE typo3.tt_content SET bodytext = replace(bodytext, 'fileadmin/templates/2iopenservice/images/check.png', 'websites/images/check.png')

ALTER TABLE openassos.tt_content ADD tx_opentalent_table_bgcolor INT(11) UNSIGNED DEFAULT '0' NOT NULL;
UPDATE openassos.tt_content SET tx_opentalent_table_bgcolor = table_bgColor;

ALTER TABLE openassos.`sys_log` CHANGE `NEWid` `NEWid` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '';

UPDATE openassos.tt_content SET imagewidth = 400, imageheight = 400 WHERE CType = 'image' AND imagewidth = 0 AND imageheight = 0;

DELIMITER $$
CREATE FUNCTION fn_RemoveHTMLTag (HtmlString text) RETURNS text
BEGIN
    DECLARE StartTag, EndTag INT DEFAULT 1;
    LOOP
        SET StartTag = LOCATE("<", HtmlString, StartTag);
        IF (!StartTag) THEN
		RETURN HtmlString;
	END IF;
        SET EndTag = LOCATE(">", HtmlString, StartTag);
        IF (!EndTag) THEN
		SET EndTag = StartTag;
	END IF;
        SET HtmlString = INSERT(HtmlString, StartTag, EndTag - StartTag + 1, "");
    END LOOP;
END;
DELIMITER ;

UPDATE openassos.sys_file_reference SET description = fn_RemoveHTMLTag(description) WHERE description LIKE '%/%';

UPDATE openassos.sys_template set config = '
<INCLUDE_TYPOSCRIPT: source="FILE:EXT:ot_portail/Configuration/TypoScript/setup.ts">

config.tx_piwik {
  piwik_idsite = 95142
  piwik_host = /typo3conf/piwik/piwik/
}
config.frontend_editing = 1
config.tx_realurl_enable = 1
config.absRefPrefix = /
config.typolinkEnableLinksAcrossDomains = 1
'
, constants = '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:ot_portail/Configuration/TypoScript/constants.ts">'
, include_static_file = 'EXT:fluid_styled_content/Configuration/TypoScript/,EXT:fluid_styled_content/Configuration/TypoScript/Styling/,EXT:form/Configuration/TypoScript/,EXT:news/Configuration/TypoScript,EXT:ot_webservice/Configuration/TypoScript,EXT:rx_shariff/Configuration/TypoScript/WithoutJQueryAndFontawesome'
where pid = 95142;