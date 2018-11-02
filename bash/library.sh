#!/bin/bash

function backupdb() {
    mysqldump -uroot -p${PASSDBROOT} ${1} --single-transaction | gzip > ${CHEMINBACKUP}/${1}_$(timestamp).sql.gz;
    return $?;
}

function remote_backupdb() {
    ssh -i ${SSHEXPLOITATIONKEY} -p ${PORT} -C exploitation@${IPPROD} mysqldump --single-transaction -u root --password=${PASSDBROOT} $1 | gzip > ${CHEMINBACKUP}/${1}_$(day_of_week).sql.gz;
    return $?;
}

function remote_backupdb_timestamp() {
    ssh -i ${SSHEXPLOITATIONKEY} -p ${PORT} -C exploitation@${IPPROD} mysqldump --single-transaction -u root --password=${PASSDBROOT} $1 | gzip > ${CHEMINBACKUP}/${1}_$(timestamp).sql.gz;
    return $?;
}

function restoredb() {
    gunzip < ${CHEMINBACKUP}/${2} | mysql -uroot -p${PASSDBROOT} ${1};
    return $?;
}

function copydb() {
    mysql -u${USERDBROOT} -p${PASSDBROOT} -e "DROP DATABASE $2;";
    mysql -u${USERDBROOT} -p${PASSDBROOT} -e "CREATE DATABASE $2;";
    if [ $? -eq 0 ]
    then
       ssh -i ${SSHEXPLOITATIONKEY} -p ${PORT} -C exploitation@${IPPROD} mysqldump --single-transaction -u ${USERDBROOTREMOTE} --password=${PASSDBROOTREMOTE} $1 | mysql -h ${3} -P ${4} -u ${USERDBROOT} --password=${PASSDBROOT} -D $2
    fi
    return $?;
}

function syncopenassos() {
    rsync --omit-dir-times -avz  --exclude "log/*" --exclude "typo3temp/*" --exclude=".svn/*" -e "ssh  -i ${SSHEXPLOITATIONKEY} -p ${PORT}" exploitation@$IPPROD:/var/www/opentalent.fr/ /var/www/$1;
    if [ $? -eq 23 -o $? -eq 24 ]
    then
        return 0;
    fi
    return $?;
}

function synclogadminassos() {
    rsync -avz -e "ssh  -i ${SSHEXPLOITATIONKEY} -p ${PORT}" exploitation@$IPPROD:/var/log/opentalent /var/log;
    if [ $? -eq 23 -o $? -eq 24 ]
    then
        return 0;
    fi
    return $?;
}

function syncnocturial() {
    rsync -avz -e "ssh  -i ${SSHEXPLOITATIONKEY} -p ${PORT}" exploitation@$IPPROD:/env/nocturial /env;
    if [ $? -eq 23 -o $? -eq 24 ]
    then
        return 0;
    fi
    return $?;
}

function syncbind() {
    cp /etc/bind/zones/opentalent.fr.db.local /etc/bind/zones/opentalent.fr.db && \
    ssh -i ${SSHEXPLOITATIONKEY} -p ${PORT} -C exploitation@${IPPROD} 'cat /etc/bind/zones/opentalent.fr.db' | sed '1,/^$/d' >>/etc/bind/zones/opentalent.fr.db && \
    /etc/init.d/bind9 reload;
    return $?;
}

function clear_typo3_cache() {
    [ -d /var/www/${1} ] &&  rm -fr /var/www/${1}/typo3temp/*;
    return $?;
}

function parse_domain_list() {
    [ -f /var/www/$2/typo3conf/domains_list.php ] && \
        perl -pi -e "s/'(www\.)*($1)'/'\$1$2'/g" /var/www/$2/typo3conf/domains_list.php
    return $?;
}

function change_login_url_scheme() {
    # database openassos_<env>
    # table pages
    # id page 86428
    # field url_scheme values 0:default 1:http 2:https
    mysql -uroot -p$PASSDBROOT -e "update pages set url_scheme=0 where uid=${PAGE_LOGOUT_UID};" -D ${1};
    return $?;
}

function lock() {
    local VERROU=$1;
    touch ${VERROU};
}

function unlock() {
    local VERROU=$1;
    rm ${VERROU};
}

function check_verrou() {
    local VERROU=$1;
    if [ -f ${VERROU} ]; then
     echo "*********************** ATTENTION **************************************"
     echo "* Systeme verrouille. Une autre operation est en cours au meme instant *"
     echo "* Veuiller reiterer votre commande dans quelques instants.             *"
     echo "************************************************************************"   
     exit 0;
    fi
}

function validation() {   
    OK=-1;
    while [ ${OK} -eq -1 ]
    do
        echo "${1:-Voulez-vous continuez ? (Repondez par O ou N puis Touche Entree)}";
        read response;
        case $response in
            [yYoO]*) OK=1;;
            [nN]*) exit 1;;
        esac
    done
    return ${OK};
}

function get_all_databases() {
    DBS=$(mysql -u${USERDBROOT} -p${PASSDBROOT} -Bse "show databases;" | grep -v "mysql\|performance_schema\|information_schema\|test");
    echo ${DBS};
}

function get_all_environnements() {    
    ENVS=$(ls -1 /var/www/ | grep "\w\+.opentalent.fr"| cut  -d "." -f1);
    echo ${ENVS};
}

function on_error_warning() {
    if [ $1 -ne 0 ]
    then
        notify-send "Warning : $2";
        echo "Warning : $2";
    fi
}

function on_error_exit() {
    if [ $1 -ne 0 ]
    then
        notify-send "Error : $2";
        echo "Error : $2";
        exit $1;
    fi
}

function timestamp() {
  date +"%s"
}

function day_of_week() {
  date +"%u"
}

function array_search() {
  local e
  for e in "${@:2}"; do [[ "$e" == "$1" ]] && return 1; done
  return 0;
}

function _n() {
    if [ $# -ne 3 ]
    then
        on_error_warning 1 "Usage : _n 'ma chaine au singulier' 'ma chaine au pluriel' <nombre à tester>";
        return 100;
    fi

    if [ $3 -le 1 ]
    then
        echo $1;
    else 
        echo $2;
    fi
}

function sendmail {
   echo ${2} |  /usr/bin/mail -s ${1} -t ${EMAILTO} -a from:${EMAILFROM}
}
