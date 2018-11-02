#!/bin/bash


source /env/common/init.sh;

export Usage="${IAM}: \
    [-d|--only-database] \
    [-n|--databases \"name1 name2\"] \
    [-f|--only-files] \
    [-e|--env \"current next\"] \
    [-z|--bind] \
    [-i|--no-interactif] \
    [-l |--logadminassos] \
    [-o |--nocturial] \
    [-H |--host \"host local db\"] \
    [-P |--port \"port local db\"] \
    [-h|--help]";

# Note that we use `"$@"' to let each command-line parameter expand to a 
# separate word. The quotes around `$@' are essential!
# We need TEMP as the `eval set --' would nuke the return value of getopt.
TEMP=$(getopt -o dbfinezhloHP --long only-database,no-interactif,only-files,databases,env,bind,logadminassos,nocturial,host,port,help: \
     -n 'synchro.sh' -- "$@")

if [ $? != 0 ] ; then echo ${Usage} >&2 ; exit 1 ; fi

all=1;
onlydb=0;
databases="crm commercial openassos adminassos portail";
onlyfiles=0;
bindzones=0;
environments="nodomain";
interactif=1;
logadminassos=0;
nocturial=0;
host='127.0.0.1';
port='3306';

while true ; do
	case "$1" in
		-d|--only-database) onlydb=1;all=0;shift ;;
		-n|--databases) databases=$2;shift 2;;
		-f|--only-files) onlyfiles=1;all=0;shift ;;                    
		-e|--env) environments=$2; shift 2;;
                -z|--bind) onlydb=1;bindzones=1;shift ;;
                -i|--no-interactif) interactif=0;shift;;
                -l|--logadminassos) logadminassos=1;shift;;
                -o|--nocturial) nocturial=1;shift;;
                -h|--help) echo ${Usage};exit 0;;
                -H|--host) host=$2;shift 2;;
                -P|--port) port=$2;shift 2;;
		*) break;;
	esac
done

check_verrou ${VERROUSYNCPROD};

if [ ${interactif} -eq 1 ]
then
    echo "Transfert des BDs de la production pour fonctionner sous $HOSTNAME";
    echo "FAITES UN BACKUP DE VOTRE TRAVAIL AVANT ! (sudo /env/backup )";
    echo "Synchronisation des fichiers";
    echo "(${envi:+${envi}"."}${DOMAIN} devient une copie parfaite de la production)";
    validation;
    check_verrou ${VERROUSYNCPROD};
fi

lock ${VERROUSYNCPROD};

for environment in ${environments}
do
    envi=${environment##nodomain};
    echo "Synchronisation de l'environnement ${envi}";
    if [ ${onlydb} -eq 1 -o ${all} -eq 1 ]
    then
        echo "Copie des bases...";
        for database in ${databases}
        do
            echo "Copie de la base ${database}${envi:+"_"${envi}}";
            copydb ${database} ${database}${envi:+"_"${envi}} ${host} ${port};
            on_error_exit $? "Sur la copie de base ($?).";
            if [ "${database}" == "openassos" ]
            then
                change_login_url_scheme "openassos${envi:+"_"${envi}}";
                on_error_warning $? "Sur l'update de la page de login ($?).\n Vous devrez changer vous même le protocole de connection pour la page de login.";
            fi
        done
    fi

    if [ ${onlyfiles} -eq 1 -o ${all} -eq 1 ]
    then
        domaine=${envi:+${envi}"."}${DOMAIN};
        echo "Synchronisation des fichiers sur ${domaine}";
        syncopenassos ${domaine} && \
        clear_typo3_cache ${domaine} && \
        parse_domain_list ${DOMAIN} ${domaine};
        on_error_exit $? "Erreur sur la synchronisation ($?).";
    fi
done

if [ ${bindzones} -eq 1 ]
then
    echo "Mise a jour des zones DNS...";
    syncbind;
    on_error_exit $? "Erreur sur la mise à jour DNS ($?).";
fi

if [ ${logadminassos} -eq 1 ]
then
    echo "Mise a jour des logs  adminassos...";
    synclogadminassos;
    on_error_exit $? "Erreur sur la mise des logs  adminassos ($?).";
fi

if [ ${nocturial} -eq 1 ]
then
    echo "Mise a jour du nocturial...";
    syncnocturial;
    on_error_exit $? "Erreur sur la mise du nocturial ($?).";
fi

unlock ${VERROUSYNCPROD};

notify-send "SYNCHRONISATION TERMINEE."
echo "SYNCHRONISATION TERMINEE.";
