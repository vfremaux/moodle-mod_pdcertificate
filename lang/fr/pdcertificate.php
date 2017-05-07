<?php

// This file is part of the Certificate module for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language strings for the pdcertificate module
 *
 * @package     mod_pdcertificate
 * @category    mod 
 * @copyright   Mark Nelson <markn@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Capabilities
$string['pdcertificate:view'] = 'Voir l\'attestation';
$string['pdcertificate:manage'] = 'Gérer l\'attestation';
$string['pdcertificate:apply'] = 'Etre attesté';
$string['pdcertificate:printteacher'] = 'Est mentionné sur l\'attestation comme formateur';
$string['pdcertificate:isauthority'] = 'Est autorité de certification';
$string['pdcertificate:getown'] = 'Retirer sa propre attestation';
$string['pdcertificate:apply'] = 'Peut obtenir une attestation';
$string['pdcertificate:deletepdcertificates'] = 'Peut détruire des attestations';
$string['pdcertificate:download'] = 'Télécharger des attestations via l\'api';

$string['addcourselabel'] = 'Ajouter un cours';
$string['addcoursetitle'] = 'Ajouter le titre du cours';
$string['addlinklabel'] = 'Ajouter un nouveau lien vers une activité';
$string['addlinktitle'] = 'Cliquer pour ajouter un nouveau lien vers une activité';
$string['antecedantcourse'] = 'Cours lié : {$a->coursename}. Prérequis obligatoire: {$a->prerequisite}';
$string['areaintro'] = 'Description';
$string['authority'] = 'Autorité';
$string['awarded'] = 'Decerné à ';
$string['awardedto'] = 'Décerné à ';
$string['back'] = 'Revenir';
$string['backtocourse'] = 'Revenir au cours';
$string['border'] = 'Bordure';
$string['borderblack'] = 'Noir';
$string['borderblue'] = 'Bleu';
$string['borderbrown'] = 'Marron';
$string['bordercolor'] = 'Couleur de bordure';
$string['bordergreen'] = 'Vert';
$string['borderlines'] = 'Lignes de bordure';
$string['borderstyle'] = 'Image de bordure';
$string['certifiableusers'] = 'Prêts à certifier:<br/><b>{$a} étudiants(s)</b>';
$string['certification'] = 'Attestations';
$string['certificationmatchednotdeliverable'] = 'Vous avez validé les attendus de ce module pour pouvoir activer votre attestation. Cependant, vous ne pouvez pas retirer par vous même l\'attestation qui doit vous être remis par votre responsable de formation.';
$string['certifiedusers'] = 'Certifiés:<br/><b>{$a} étudiants(s)</b>';
$string['certifierid'] = 'Autorité attestante';
$string['chaining'] = 'Chaînage';
$string['clearprintborders'] = 'Supprimer ce fichier';
$string['clearprintseal'] = 'Supprimer ce fichier';
$string['clearprintsignature'] = 'Supprimer ce fichier';
$string['clearprintwmark'] = 'Supprimer ce fichier';
$string['code'] = 'Code';
$string['completiondate'] = 'Achèvement du cours';
$string['completiondelivered'] = 'L\'attestation doit être effectivement délivrée (retirée par le candidat ou postée) pour marquer comme achevé';
$string['course'] = 'pour le cours';
$string['coursechaining'] = 'Chaînage de cours';
$string['coursedependencies'] = 'Cours dépendants';
$string['courseenddate'] = 'Date de fin de formation (doit être renseignée!)';
$string['coursegrade'] = 'Note des cours';
$string['coursename'] = 'Nom du Cours';
$string['coursetime'] = 'Crédit horaire de formation requis';
$string['coursetimedependency'] = 'Temps minimum requis dans le cours';
$string['coursetimereq'] = 'Minutes minimum dans le cours';
$string['credithours'] = 'Crédit d\'heures';
$string['customtext'] = 'Texte personnalisé';
$string['date'] = 'le';
$string['datefmt'] = 'Format de date';
$string['datehelp'] = 'Date';
$string['defaultpropagategroups'] = 'Propagation des groupes (défaut)';
$string['defaultpropagategroups_desc'] = 'Si coché, les informations de groupe seront copiées dans le cours chainé lors du passage des utilisateurs.';
$string['definitive'] = 'Valide (définitif)';
$string['deletissuedpdcertificates'] = 'Supprimer les attestations délivrées';
$string['deliveredon'] = 'Retiré le';
$string['delivery'] = 'Délivrance';
$string['designoptions'] = 'Mise en forme';
$string['description'] = 'Description';
$string['destroyselection'] = 'Détruire les attestations ';
$string['download'] = 'Forcer le téléchargement';
$string['emailothers'] = 'Autres destinataires';
$string['emailpdcertificate'] = 'Mél (doit être sauvegardé!)';
$string['emailstudenttext'] = 'Votre attestation pour le cours {$a->course} est joint en pièce attachée.';
$string['emailteachers'] = 'Envoyer un mél aux formateurs';
$string['entercode'] = 'Entrer le code de l\'attestation à vérifier :';
$string['errorinvalidinstance'] = 'Erreur : cette instance d\'attestation n\'existe pas';
$string['errornocapabilitytodelete'] = 'vous n\'avez pas les capacités pour détruire des attestations';
$string['expiredon'] = 'Expiré le';
$string['followercourse'] = 'Cours lié : {$a->rolename} dans {$a->coursename}. Ce cours est prérequis : {$a->prerequisite}';
$string['followers'] = 'Module(s) suivant(s) du parcours ';
$string['footertext'] = 'Texte personnalisé du pied de page';
$string['freemono'] = 'Monospace';
$string['freesans'] = 'Sans sérif';
$string['freeserif'] = 'Sérif';
$string['generate'] = 'Générer';
$string['generateall'] = 'Générer les {$a} attestations disponibles';
$string['generateselection'] = 'Générer les attestations ';
$string['getattempts'] = 'Retirer mes attestations';
$string['getpdcertificate'] = 'Obtenez votre attestation';
$string['gettestpdcertificate'] = 'Tester le retrait d\'attestation';
$string['grade'] = 'avec la note';
$string['gradedate'] = 'Date des évaluations';
$string['gradefmt'] = 'Format de note';
$string['gradeletter'] = 'Barème lettre';
$string['gradepercent'] = 'Barème en pourcentages';
$string['gradepoints'] = 'Barèmes par points';
$string['groupspecificcontent'] = 'Information spécifique au groupe';
$string['headertext'] = 'Texte personnalisé de l\'en-tête';
$string['imagetype'] = 'Type d\'image';
$string['incompletemessage'] = 'Afin de télécharger l\'attestation, vous devez d\'abord avoir terminé toutes les activités requises. Veuillez retourner dansvos parcours pour terminer les activités pédagogiques proposées.';
$string['intro'] = 'Introduction';
$string['invalidcode'] = 'Code invalide';
$string['issued'] = 'Validé';
$string['issueddate'] = 'Date de validation';
$string['issueoptions'] = 'Comportement à la délivrance';
$string['landscape'] = 'Paysage';
$string['lastviewed'] = 'Vous avez visualisé cette attestation le :';
$string['letter'] = 'Letter';
$string['linkedactivity'] = 'Activités liées';
$string['linkedcourse'] = 'Cours';
$string['linkedcourse'] = 'Lié au cours';
$string['lockingoptions'] = 'Conditions d\'acquisition';
$string['lockoncoursecompletion'] = 'Sensible à l\'achèvement de cours';
$string['managedelivery'] = 'Gérer la délivrance';
$string['mandatoryreq'] = 'Prérequis obligatoire';
$string['migrate'] = 'Migrer les certificats';
$string['migration'] = 'Assistant de migration des certificats';
$string['modulename'] = 'Attestation Pro';
$string['modulenameplural'] = 'Attestations';
$string['mypdcertificates'] = 'Mes attestations';
$string['margins'] = 'Marges (x,y)';
$string['manualenrolnotavailableontarget'] = 'La méthode manuelle d\'inscription semble avoir été désactivée. Le chainage ne peut être réalisé.';
$string['needsmorework'] = 'Il y a encore des travaux requis pour activer cette attestation';
$string['noauthority'] = 'Pas d\'autorité';
$string['nocertifiables'] = 'Aucun utilisateur à attester';
$string['nofileselected'] = 'Vous devez choisir un fichier !';
$string['nogrades'] = 'Aucune évaluation disponible';
$string['none'] = '(Aucun)';
$string['nopdcertificates'] = 'Aucune attestation';
$string['nopdcertificatesissued'] = 'Aucune attestation n\'a été délivrée';
$string['nopdcertificatesreceived'] = 'n\'a jamais reçu d\'attestation.';
$string['notapplicable'] = 'N/A';
$string['notfound'] = 'Le numéro d\'attestation n\'a pas pu être validé.';
$string['notissued'] = 'Non validé';
$string['notissuedyet'] = 'Non encore validé';
$string['notreceived'] = 'Vous n\'avez pas reçu cette attestation';
$string['notyetcertifiable'] = 'Non prêt';
$string['notyetusers'] = 'Non certifiables:<br/><b>{$a} étudiants(s)</b>';
$string['openbrowser'] = 'ouvrir dans une nouvelle fenêtre';
$string['opendownload'] = 'Cliquez sur le bouton ci-dessous pour sauvegarder cette attestation sur votre ordinateur.';
$string['openemail'] = 'Cliquez sur le bouton ci-dessous pour recevoir votre attestation par mél.';
$string['openwindow'] = 'Cliquez le bouton ci-dessous pour voir votre attestation dans un nouveau navigateur.';
$string['or'] = 'Or';
$string['orientation'] = 'Orientation';
$string['pdcertificate'] = 'Vérification du code de l\'attestation :';
$string['pdcertificatecaption'] = 'Titre de l\'attestation';
$string['pdcertificatedefaultlock'] = 'Les attestations sont verrouillées par défaut';
$string['pdcertificatefile'] = 'Attestation (Document) ';
$string['pdcertificatefilenoaccess'] = 'Vous devez avoir un compte valide et être connecté pour accéder à cette information.';
$string['pdcertificatelock'] = 'Verrou';
$string['pdcertificatename'] = 'Nom de l\'attestation';
$string['pdcertificateremoved'] = 'Attestation supprimée';
$string['pdcertificatereport'] = 'Rapport des attestations';
$string['pdcertificatesfor'] = 'Attestation pour';
$string['pdcertificatetype'] = 'Modèle d\'attestation';
$string['pdcertificateverification'] = 'Vérification de l\'attestation';
$string['pdcertificateverifiedstate'] = 'Le code d\'attestation que vous avez demandé est reconnu et correspond à l\'enregistrement ci-dessous :';
$string['pluginadministration'] = 'Administration de l\'attestation';
$string['pluginname'] = 'Attestation de formation';
$string['portrait'] = 'Portrait';
$string['prerequisites'] = 'Prérequis';
$string['previewpdcertificate'] = 'Prévisualiser l\'attestation';
$string['printborders'] = 'Fond ou bordures';
$string['printerfriendly'] = 'Version imprimable';
$string['printfontfamily'] = 'Police';
$string['printfontsize'] = 'Taille de police de base';
$string['printhours'] = 'Forfait d\'heures à reporter';
$string['printoptions'] = 'Options d\'impression';
$string['printoutcome'] = 'Imprimer l\'objectif atteint';
$string['printoutcome'] = 'Objectif à mentionner';
$string['printqrcode'] = 'Imprimer un QR code';
$string['printseal'] = 'Image sceau ou logo';
$string['printsignature'] = 'Image de signature';
$string['printteacher'] = 'Imprimer le nom du formateur';
$string['printwmark'] = 'Filigrane';
$string['printdateformat'] = '';
$string['propagategroups'] = 'Propagation des groupes';
$string['qrcodeoffset'] = 'Position du QR code (x,y)';
$string['receivedcerts'] = 'Attestations reçus';
$string['receiveddate'] = 'Date de réception';
$string['regenerate'] = 'Régénérer';
$string['releaseselection'] = 'Valider les attestations ';
$string['removecert'] = 'Les attestations ont été détruites';
$string['report'] = 'Rapport';
$string['reportcert'] = 'Rapports sur les attestations';
$string['requiredcoursecompletion'] = 'Vous devez avoir achevé l\'ensemble des objectifs du cours pour pouvoir retirer votre certificat.';
$string['requiredtimenotmet'] = 'Vous devez avoir passé au moins {$a->requiredtime} minutes dans ce cours avant de pouvoir retirer votre attestations.';
$string['requiredtimenotvalid'] = 'Le temps passé doit être une grandeur supérieure à 0';
$string['reviewpdcertificate'] = 'Revoir votre attestation';
$string['rolereq'] = 'Role';
$string['savecert'] = 'Sauvegarder l\'attestation';
$string['seal'] = 'Sceau';
$string['sealoffset'] = 'Position du sceau (x,y)';
$string['setcertification'] = 'Role donné sur délivrance';
$string['setcertificationcontext'] = 'Contexte';
$string['sigline'] = 'ligne';
$string['signature'] = 'Signature';
$string['signatureoffset'] = 'Position de la signature (x,y)';
$string['sitecourse'] = 'La page d\'accueil';
$string['specialgroupoptions'] = 'Options spéciales relatives au groupes';
$string['state'] = 'Statut';
$string['statement'] = 'a achevé le cours';
$string['summary'] = 'Résumé';
$string['system'] = 'Niveau système';
$string['teacherview'] = 'Outils de l\'enseignant';
$string['textoptions'] = 'Options de texte';
$string['thiscategory'] = 'Cette catégorie';
$string['thiscourse'] = 'Ce cours';
$string['title'] = 'ATTESTATION DE PARTICIPATION';
$string['to'] = 'Décernée à ';
$string['totalcount'] = 'Utilisateurs concernés';
$string['tryothercode'] = 'Essayer un autre code';
$string['typeA4_embedded'] = 'A4 avec polices';
$string['typeA4_non_embedded'] = 'A4 sans polices';
$string['typeletter_embedded'] = 'Letter US avec polices';
$string['typeletter_non_embedded'] = 'Letter US sans polices';
$string['unsupportedfiletype'] = 'Le fichier doit être une image jpg ou png';
$string['uploadimage'] = 'Télécharger une image';
$string['uploadimagedesc'] = 'Ce bouton vous amène à un autre écran où vous pouvez téléverser une image.';
$string['userdateformat'] = 'Format de date de l\'utilisateur';
$string['usersdelivered'] = 'Attestations délivrées : {$a}';
$string['usersgenerated'] = 'Attestations générées non retirées&nbsp;: {$a}';
$string['userstocertify'] = 'Reste à attester : {$a}';
$string['validate'] = 'Vérifier';
$string['validity'] = 'Validité';
$string['validitytime'] = 'Temps de validité';
$string['validuntil'] = 'Valide jusque ';
$string['verifypdcertificate'] = 'Vérifier l\'attestation';
$string['view_pageitem_directlink_to_follower'] = 'Widget avec lien direct vers les cours suivants';
$string['viewall'] = 'Voir tout';
$string['viewalladvice'] = 'Attention ! de grands groupes d\'apprenants peuvent générer une très forte charge sur le serveur et votre navigateur';
$string['viewed'] = 'Vous avez reçu cette attestation le :';
$string['viewless'] = 'En voir moins';
$string['viewpdcertificateviews'] = 'Voir les {$a} attestations délivrées';
$string['viewtranscript'] = 'Voir les attestations';
$string['watermark'] = 'Filigrane';
$string['withsel'] = 'Avec la sélection&nbsp;:&ensp;';
$string['wmarkoffset'] = 'Position du filigrane (x,y)';
$string['yetcertifiable'] = 'Prêts à générer';
$string['yetcertified'] = 'Générés';
$string['youcango'] = 'Vous pouvez continuer votre parcours dans ce module';
$string['youcantgo'] = 'Vous ne remplissez pas encore les conditions pour atteindre ce module';

$string['unlimited'] = "Illimité";
$string['oneday'] = "Un jour";
$string['oneweek'] = "Une semaine";
$string['onemonth'] = "Un mois";
$string['threemonths'] = "Trois mois";
$string['sixmonths'] = "Six mois";
$string['oneyear'] = "Un an";
$string['twoyears'] = "Deux ans";
$string['threeyears'] = "Trois ans";
$string['fiveyears'] = "Cinq ans";
$string['tenyears'] = "Dix ans";

// Help strings

$string['validitytime_help'] = 'L\'attestation sera déclarée comme invalide à la vérification après ce délai à compter de sa date d\'émission.';

$string['coursetimereq_help'] = 'Entrez le temps minimum en minutes, que le candidat doit passer connecté à ce cours avant de délivrer l\'attestation.';

$string['datefmt_help'] = 'Choisir un format de date pour l\'impression. Vous pouvez aussi demander à ce que la date soit imprimée au format standard correspondant à la langue du candidat.';

$string['emailothers_help'] = 'Entrez les adresses de courriel, séparées par des virgules, des personnes qui doivent être alertée de la délivrance des attestations.';

$string['printwmark_help'] = 'Un fichier filigrane peut être imprimé en fond de l\'attestation. Un filigrane est une image dont le contraste a été poussé pour être très claire. Il peut s\'agir d\'un sigle, écusson ou tout autre image pouvant servir de fond.';

$string['printoutcome_help'] = 'Vous pouvez choisir d\'afficher une mention d\'objectif. Ceci suppose de placer la balise {info:certificate_outcome} dans les textes du certificat';

$string['printhours_help'] = 'Entrez le nombre d\'heures créditées de formation à afficher sur l\'attestation. Ce nombre d\'heures est une mention manuelle car il ne correspond pas nécessairement au temps réellement passé par le candidat. Ceci suppose placer la balise {info:certificate_credit_hours} dans les textes de l\'attestation';

$string['printqrcode_help'] = 'Un QR code scannable est imprimé sur l\'attestation. Code contient une URL de redirection vers une page de vérification de la validité de l\'attestation.';

$string['printseal_help'] = 'Vous pouvez imprimer un sceau ou logo sur l\'attestation. Par défaut cette image est dans le coin inférieur gauche du document .';

$string['printsignature_help'] = 'Vous pouvez imprimer une signature numérisée sur l\'attestation. Vous pouvez utiliser une signature prénumérisée, ou par défaut une simple ligne pour une signature manuelle. Par défaut, la signature est positionnée en bas à droite du document.';

$string['reportcert_help'] = 'Si vous activez cette option, aors les dates de réception, numéro de code, et le nom du cours seront mentionnés dans les rapports. Si vous avez opté pour la mention du score, alors le score sera également affiché dans les rapports.';

$string['savecert_help'] = 'Si vous activez cette option, alors une copie pdf de l\'attestation est stockée physiquement dans les fichiers du module. Un lien vers ces fichiers sera disponible dans les rapports d\'attestation pour chaque candidat attesté.';

$string['pdcertificatelock_help'] = 'Vous pouvez verrouiller les certificats constitués, bloquant ainsi leur délivrance le temps qu\'une condition externe soit réalisée (par exemple un paiement par la boutique intégrée)';

$string['emailteachers_help'] = 'Si activé, les enseignants recevront une notification par couriel dès qu\'un candidat reçoit ou retire son attestation.';

$string['pdcertificatetype_help'] = 'Vous déterminez ici la mise en forme de l\'attestation. Le répertoire des modèles d\'attestation contient quatre sous-répertoires de modèles par défaut :

A4 avec polices : imprime l\'attestation au format A4 avec inclusion des polices de caractères.

A4 sans polices : imprime l\'attestation au format A4 sans inclusion des polices de caractères.

Lettre US avec polices : imprime l\'attestation au format Letter US sans inclusion des polices de caractères.

Lettre US sans polices : imprime l\'attestation au format Letter US sans inclusion des polices de caractères.

Le modèle sans polices incluses fait référence aux polices Helvetica et Times. Si vous savez que vos utilisateurs ne disposent pas de manière fiable des polices Helvetica et Times sur leur ordinateur, ou si l\'impression utilise des caractères non compatibles avec ces polices, alors il est conseillé d\'utiliser les formats à polices incluses. Le format à policss incluses contient les polices Dejavusans et Dejavuserif. Ceci accroit de manière significative la taille des fichiers PDF générés. N\'utilisez le transport des polices que si vous ne pouvez faire autrement.

Il est possible de customiser ces modèle en ajoutant des répertoires. Demandez à un intégrateur Moodle de constituer les ressources nécessaires.';

$string['headertext_help'] = '';
$string['customtext_help'] = '';
$string['footertext_help'] = '';

$string['delivery_help'] = 'Choisissez la façon dont le candidat recevra son attestation.

Ouvrir dans le navigateur : Visualise l\'atttestaton dans une fenêtre du navigateur.

Force le télécargement : Déclenche l\'ouverture d\une popup de téléchargement dans le navigateur.

Par courriel : L\'attestation est envoyée comme pièce attachée d\'un courriel.
Une fois que l\'attestation a été envoyée, les candidats pourront retrouver la date effective d\'envoi et visualiser l\'attstation par un lien dans la page principale du cours.
';

$string['emailteachermail'] = '
{$a->student} a reçu son attestation : \'{$a->pdcertificate}\'
pour le cours {$a->course}.

Vous pouvez la visualiser ici :

    {$a->url}';

$string['emailteachermailhtml'] = '
{$a->student} a reçu son attestation : \'<i>{$a->pdcertificate}</i>\'
pour le cours {$a->course}.

Vous pouvez la visualiser ici :

    <a href="{$a->url}">Rapport d\'attestation</a>.';

$string['gradefmt_help'] = 'Trois formats de score sont disponibles si vous voulez imprimer le score sur l\'attestation :

Pourcentage : Affiche un pourcentage de réalisation.

Points : La note est affichée en valeur absolue de points reçus.

Lettrage : Un barème à lettres est utilisé.
';

$string['propagategroups_help'] = 'Si coché, les informations de groupe seront copiées dans le cours chainé lors du passage des utilisateurs.';

$string['groupspecificcontent_help'] = 'Cette option recherche un bloc HTML spécifique au groupe pour y trouver une information à mentionner sur l\'attestation.';

$string['lockoncoursecompletion_help'] = 'Si cette case est cochée, le certificat ne peut être produit si le cours courant n\'est pas achevé.';

$string['setcertificationcontext_help'] = 'Le contexte dans lequel le rôle sera donné sur attestation';

$string['certifierid_help'] = 'Définir une autorité attestante imprimera le nom de l\'autorité sur l\'attestation si son modèle le permet';

$string['setcertification_help'] = 'Le rôle qui sera attribué lors de la délivrance. Notez qu\'il ne s\'agit pas d\'une inscription. Pour inscrire le bénéficiaire à un nouveau cours, vous devrez utiliser le chainage de cours.';

$string['chaining_help'] = 'Le chaînage permet à un bénéficiaire d\'être inscrit dans un nouveau cours comme conséquence de la délivrance de l\'attestation';

