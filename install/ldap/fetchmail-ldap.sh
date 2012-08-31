#!/bin/sh
ldap_base="dc=iabsis,dc=local"
ldap_users="ou=Users,dc=iabsis,dc=local"
ldap_login="cn=admin,dc=iabsis,dc=local"
ldap_passwd="pfcqopfs"
ldap_host="localhost"
retry=3

send_mail() {

content=$(echo -e "L'erreur suivante s'est produite pendant la tentative de récupération de vos mail : \n\n $exec \n\n Accédez à l'interface iGestis pour corriger ce problème" | iconv --from-code=UTF-8 --to-code=ISO-8859-1 | txt2html)

(
echo "From: noanswer@ishare.homelinux.com"
echo "To: $account "
echo "MIME-Version: 1.0"
echo "Content-Type: multipart/alternative; " 
echo ' boundary="PAA08673.1018277622/ishare.homelinux.com"' 
echo "Subject: Erreur sur un de vos compte mail." 
echo "" 
echo "This is a MIME-encapsulated message" 
echo "" 
echo "--PAA08673.1018277622/ishare.homelinux.com" 
echo "Content-Type: text/html" 
echo "" 
echo "$content"
echo "--PAA08673.1018277622/ishare.homelinux.com"
) | /usr/lib/sendmail -t

}

fetchmail_ldap() {

loop=0

while ( ! $loop -lt $retry ) ; do

  case $fetchmail_keep in
    true)
      exec=$(echo "poll $fetchmail_server protocol $fetchmail_protocol username \"$fetchmail_login\" password \"$fetchmail_passwd\" keep to $user" | fetchmail -f - 2>&1)
      RET=$?
    ;;
    false)
      exec=$(echo "poll $fetchmail_server protocol $fetchmail_protocol username \"$fetchmail_login\" password \"$fetchmail_passwd\" to $user" | fetchmail -f - 2>&1)
      RET=$?
    ;;
  esac

sleep 10

[ "$RET" = 0 ] && return

done

send_mail

}

users_ldif=$(ldapsearch -x -b "$ldap_users" -H "$ldap_host" -D "$ldap_login" -w "$ldap_passwd" -LLL "(objectclass=posixAccount)" uid)
users=$(echo "$users_ldif" | grep "uid" | sed "s/uid: //g")

for user in $users ; do

  fetchmail_accounts_ldif=$(ldapsearch -x -b "uid=$user,$ldap_users" -H "$ldap_host" -D "$ldap_login" -w "$ldap_passwd" -LLL "(objectclass=Fetchmail)" dn)
  fetchmail_accounts=$(echo "$fetchmail_accounts_ldif" | grep "dn" | sed "s/dn: //g")

    for account in $fetchmail_accounts ; do

	fetchmail_cred_ldif=$(ldapsearch -x -b "$account" -H "$ldap_host" -D "$ldap_login" -w "$ldap_passwd" -LLL "(objectclass=Fetchmail)" dn)
	fetchmail_login=$(echo "$fetchmail_cred_ldif" | grep "FetchmailUser" | sed "s/FetchmailUser: //g")
	fetchmail_passwd=$(echo "$fetchmail_cred_ldif" | grep "FetchmailPasswd" | sed "s/FetchmailPasswd: //g")
	fetchmail_protocol=$(echo "$fetchmail_cred_ldif" | grep "FetchmailProtocol" | sed "s/FetchmailProtocol: //g")
	fetchmail_server=$(echo "$fetchmail_cred_ldif" | grep "FetchmailServer" | sed "s/FetchmailServer: //g")
	fetchmail_keep=$(echo "$fetchmail_cred_ldif" | grep "FetchmailKeepMailOnServer" | sed "s/FetchmailKeepMailOnServer: //g")
	
	fetchmail_ldap

    done

done

