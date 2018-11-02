#!/bin/bash

# Synchronisation du bac à sable (test.opentalent.fr)

sandbox_folder="/var/www/opentalent.fr";

if [ ! -d ${sandbox_folder} ]
then
    echo "Le bac à sable opentalent n'existe pas !!!";
    exit 1;
fi

# Synchronisation du bac à sable
echo "Début de la synchronisation des fichiers et base de données.";

/env/synchro.sh  --no-interactif

ret=$?;
if [ $ret -ne 0 ]
then
    echo "Problème sur la synchronisation !!!";
    exit $ret;
fi

# Ajout du fichier robots.txt
echo -e "User-agent: *\nDisallow: /" > ${sandbox_folder}/robots.txt

# Désactivation de realurl, ce n'est pas une super façon de faire, mais cela évite de modifier le typoscript de chaque page pour mettre 
# config.tx_realurl_enabled = 0
# Une fois que les thémes seront en place, il y aura une façon plus élégante de faire cela.
echo -e "<?php \$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['linkData-PostProc']['tx_realurl'] = 'TYPO3\CMS\Core\Cache\Backend\NullBackend->flush'; ?>" >> ${sandbox_folder}/typo3conf/realurl_autoconf.php

# Positionnement d'un tip permettant à l'utilisateur d'être informé de la mise à jour.
# Start Tip
php -r '
chdir("/var/www/opentalent.fr");
set_include_path(get_include_path() . PATH_SEPARATOR . "/var/www/opentalent.fr");
$_SERVER['HTTP_HOST'] = "test.opentalent.fr";
$_SERVER["OPENTALENT_ENV"] = "local";
require_once ("opentalent/init.php");
try {
    $s_date_start = date("d-m-Y",time());
    $s_date_end = date("d-m-Y",strtotime("+7 day"));
    $s_tip_message = sprintf("Le bac à sable est une image de la production en date du %s,
    vous pouvez faire tous les tests que vous voulez...
    Les données seront remises à jours le %s.<br/>
    <FONT COLOR=\"red\">Tous les mails envoyés à partir de cette plateforme, seront envoyés sur votre adresse mail de connection,
    il s\"agit de mail de simulation et les destinataires ne les reçevront pas...</FONT>
    ",$s_date_start,$s_date_end);
    $a_infos = array(
        "Synchronisation du bac à sable",
        $s_date_start,
        $s_date_end,
        1,
        $s_tip_message
    );
    $o_tip = new Oa_Tips();
    $a_retables = $o_tip->createTips($a_infos);
}
catch(Exception $ex) {
    echo $ex->getMessage();
    exit(1);
}
exit(0);
';
# End Tip

ret=$?;
if [ $ret -ne 0 ]
then
    echo "Problème sur la création du tip !!!";
    exit $ret;
fi

echo "Création du tip ok.";

echo "Synchronisation terminée";
exit 0;