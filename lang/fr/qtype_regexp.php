<?php
$string['addahint'] = 'Ajouter un indice';
$string['addmoreanswers'] = 'Ajouter {no} réponse';
$string['answer'] = 'Réponse :';
$string['answermustbegiven'] = 'Ce champ Réponse ne peut pas être vide si vous avez entré une valeur de Score ou un message de Feedback.';
$string['answer1mustbegiven'] = 'La Réponse n° 1 ne peut pas être vide.';
$string['answerno'] = 'Réponse {$a}';
$string['addingregexp'] = 'Créer une question de type expression régulière';
$string['bestcorrectansweris'] = '<strong>La meilleure réponse est :</strong><br />{$a}';
$string['caseno'] = 'La casse des caractères indiffère';
$string['casesensitive'] = 'Casse des caractères';
$string['caseyes'] = 'La casse des caractères doit être respectée';
$string['clicktosubmit'] = 'Cliquez le bouton <strong>Vérifier</strong> pour Envoyer cette réponse <strong>correcte</strong>.';
$string['correctansweris'] = '<strong>La réponse correcte est :</strong><br />{$a}.';
$string['correctanswersare'] = '<strong>Les autres réponses acceptables sont :</strong>';
$string['editingregexp'] = 'Modifier une question de type expression régulière';
$string['filloutoneanswer'] = '<strong>Réponse 1</strong> doit être correcte (Note = 100%) et ne sera <strong>pas</strong> analysée en tant qu\'expression régulière.';
$string['hidealternate'] = 'Masquer les réponses alternatives';
$string['illegalcharacters'] = '<strong>ERREUR !</strong> Dans les Réponses avec un score supérieur à 0%, ces métacaractères non <em>échappés</em> ne sont pas autorisés :<strong>{$a}</strong>';
$string['letter'] = 'Lettre';
$string['notenoughanswers'] = 'Ce type de question demande au moins une réponse';
$string['penaltyforeachincorrecttry'] = 'Pénalité pour essai incorrect ou Aide';
$string['penaltyforeachincorrecttry_help'] = 'Lorsque vous utilisez le mode «&nbsp;Interactif avec tentatives multiples » ou «&nbsp;Adaptatif&nbsp;» 
les étudiants ont plusieurs essais pour trouver la bonne réponse. Cette option contrôle comment ils seront pénalisés pour chaque essai incorrect.

La pénalité est un pourcentage de la note totale de la question, donc si la question est notée sur 3 points et que la pénalité est de 0,33, alors l\'étudiant aura 3 points s\'il répond correctement à la question au premier essai, 
2 points s\'il répond correctement au deuxième essai, et 1 point s\'il répond correctement au troisième essai.

Si vous avez sélectionné comme mode d\'aide pour cette question <strong>Lettre</strong> ou <strong>Mot</strong>,
 la valeur indiquée pour la pénalité s\'appliquera également à tout «&nbsp;achat&nbsp;» de lettre ou de mot.';
$string['pleaseenterananswer'] = 'Veuillez entrer votre réponse.';
$string['pluginname'] = 'Réponse courte de type expression régulière';
$string['pluginname_help'] = 'Clic droit sur le lien <em>Aide</em> ci-dessous pour ouvrir la page d\'aide de la documentation Moodle.';
$string['pluginname_link'] = 'question/type/regexp';
$string['pluginnameadding'] = 'Créer une question de type expression régulière';
$string['pluginnameediting'] = 'Modifier une question de type expression régulière';
$string['pluginnamesummary'] = 'Question à réponse courte où les réponses de l\'étudiant sont basées sur des expressions régulières';
$string['regexp'] = 'Réponse courte de type <br />expression régulière';
$string['regexp_help'] = 'Clic droit sur le lien <em>Aide</em> ci-dessous pour ouvrir la page d\'aide de la documentation Moodle.';
$string['regexp_link'] = 'question/type/regexp';
$string['regexperror'] = 'Erreur dans votre expression régulière&nbsp;: <strong>{$a}</strong>';
$string['regexperrorclose'] = 'fermant(e)s: <strong>{$a}</strong>';
$string['regexperroropen'] = 'ouvrant(e)s: <strong>{$a}</strong>';
$string['regexperrorparen'] = '<strong>ERREUR !</strong> Vérifiez vos parenthèses ou crochets carrés !';
$string['regexperrorsqbrack'] = 'Crochets carrés';
$string['regexpsensitive'] = 'Utiliser les expressions régulières pour analyser les réponses';
$string['regexpsummary'] = 'Question à réponse courte où les réponses de l\'étudiant sont basées sur des expressions régulières';
$string['settingsformultipletries'] = 'Paramètres de pénalités pour les essais incorrects et l\'achat de lettres ou de mots';
$string['showalternate'] = 'Afficher les réponses alternatives';
$string['showhidealternate'] = 'Afficher/Masquer les réponses alternatives';
$string['showhidealternate_help'] = 'Calculer et afficher toutes les réponses alternatives correctes sur cette page&nbsp;? Cette opération peut surcharger votre serveur 
selon le nombre et la complexité des expressions régulières que vous avez créées dans les champs Réponse.

Cependant, afficher ces réponses alternatives maintenant est la meilleure façon de vérifier que vos expressions régulières sont correctement rédigées.';
$string['studentshowalternate'] = 'Montrer les réponses alternatives à l\'étudiant';
$string['studentshowalternate_help'] = 'Montrer <strong>toutes</strong> les réponses alternatives correctes à l\'étudiant sur la page "Relecture"&nbsp;? S\'il y a beaucoup  
de réponses alternatives générées automatiquement, la page peut devenir très longue...';
$string['usehint'] = 'Mode d\'aide';
$string['usehint_help'] = 'Si un mode d\'aide autre que <strong>Aucun</strong> est sélectionné, un bouton d\'aide sera affiché 
pour permettre à l\'étudiant d\'«&nbsp;acheter&nbsp;» une lettre ou un mot.

En mode <strong>Adaptif</strong>, le bouton d\'aide affichera «&nbsp;Acheter la lettre suivante&nbsp;» ou «&nbsp;Acheter le mot suivant&nbsp;» selon 
le mode sélectionné par l\'enseignant. Pour la valeur de la pénalité d\'achat d\'une lettre ou d\'un mot, 
voir le paramétrage <strong>plus bas sur cette page</strong>.

En mode <strong>Adaptif sans pénalité</strong>, le bouton d\'aide affichera «&nbsp;Demander la lettre suivante&nbsp;» ou «&nbsp;Demander le mot suivant&nbsp;»

La valeur par défaut du paramètre <strong>Mode d\'aide</strong> est <strong>Aucun</strong>.';
$string['word'] = 'Mot';
?>