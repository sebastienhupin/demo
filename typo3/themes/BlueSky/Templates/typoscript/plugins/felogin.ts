/*
 *    Project:  portail - portail
 *    Version:  1.0.0
 *    Date:    Jan 22, 2015 10:14:11 AM
 *    Author:  Sébastien Hupin <sebastien.hupin at 2iopenservice.fr> 
 *
 *    Coded with Netbeans!
 */

#| --- Plugin ----------------------------------------------------------------------------------------- |#


#############################################
# PLUG-IN felogin box
plugin.tx_felogin_pi1.storagePid = 2

#############################################
# PLUG-IN felogin box
plugin.tx_felogin_pi1.templateFile = fileadmin/theme_gallery/BlueSky/Templates/Partials/felogin.html
#plugin.tx_felogin_pi1.templateFile = fileadmin/templates/felogin_openassos.html
plugin.tx_felogin_pi1.email_from = contact@opentalent.fr
plugin.tx_felogin_pi1.email_fromName = Opentalent
plugin.tx_felogin_pi1.replyTo = contact@opentalent.fr
plugin.tx_felogin_pi1 {
    showForgotPassword = 1
   showForgotPasswordLink = 1
   exposeNonexistentUserInForgotPasswordDialog = 1
   redirectMode = 1
   redirectDisable = 0
   wrapContentInBaseClass = 1
  linkConfig {
    forceAbsoluteUrl = 1
  }
   welcomeMessage_stdWrap {
    wrap = <div><p>|</p></div>
  }
   successMessage_stdWrap {
    wrap = <div><p>|</p></div>
  }
   logoutMessage_stdWrap {
    wrap = <div><p>|</p></div>
  }
   changePasswordMessage_stdWrap {
    wrap = <div><p>|</p></div>
  }
   _LOCAL_LANG.fr {
    ll_welcome_header = Connexion
    ll_welcome_message = 
    ll_logout_header = Vous êtes déconnecté
    ll_logout_message = Vous vous êtes déconnecté avec succès. Vous pouvez vous identifier à nouveau en utilisant le formulaire ci-dessous.
    ll_error_header = Identification incorrecte
    ll_error_message = Une erreur est survenue durant la connexion. Vraisemblablement, le nom d'utilisateur ou le mot de passe étaient faux. Faites attentions à être précis, en particulier en ce qui concerne l'usage des minuscules et des majuscules. Il est aussi possible que les cookies soient désactivés.
    ll_success_header = Identification correcte
    ll_success_message = Vous êtes maintenant identifié en temps que '###USER###'
    ll_header_status = Etat actuel
    ll_status_message = Votre état actuel: Connecté
    cookie_warning = Attention: l'utilisation des cookies semble désactivé dans les options de votre navigageur ! Si votre nom d'utilisateur disparait au prochain clique, vous devrez autoriser les cookies (ou accepter les cookies pour ce site).
    
    username = Identifiant
    password = Mot de passe
    login = Accèder au Logiciel
    logout = Déconnexion
    
    reset_password = Envoyer
    ll_enter_your_data = Adresse e-mail:
    ll_forgot_header = Mot de passe oublié ?
    ll_forgot_reset_message = Entrez votre adresse e-mail avec laquelle vous êtes enregistré. Cliquez sur "envoyer", et votre mot de passe vous sera expédié immédiatement. Vérifiez bien votre adresse e-mail avant d'envoyer votre demande.
    ll_forgot_reset_message_emailSent = Votre mot de passe a été envoyé à votre adresse email
    ll_forgot_header_backToLogin = Retour à l'identification
    ll_forgot_validate_reset_password (
Votre mot de passe

Bonjour %s

Votre identifiant sur OpenAssos est: %s

Votre mot de passe est : %s

    )
    ll_forgot_header_backToLogin = Retourner au formulaire d'identification
    ll_forgot_reset_message_error (
       Nous n'avons pas trouvé d'utilisateur pour ce mot de passe et donc ne nous pouvons vous l'envoyer.
       Vérifiez votre adresse email ou alors vous n'êtes pas encore enregistré ?

    )

    forgot_password_pswmsg_severalusername_header (

Vos codes d'accès

Bonjour %s

Vous disposez de plusieurs comptes pour cette même adresse email.
Voici vos codes d'accès:

    )
    forgot_password_pswmsg_severalusername_content (

---------------------------------------------
COMPTE OPEN ASSOS N° %s
-----------------------
Votre identifiant est: %s

Votre mot de passe est : %s
---------------------------------------------

    )
   }
}

lib.formlogin < plugin.tx_felogin_pi1

