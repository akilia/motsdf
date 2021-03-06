# Plugin Mots clés Dans Formulaire

## Objectif
Afficher les mots-clés d'un groupe dans le mode ÉDITION d’un formulaire d'objet éditorial.

## Pourquoi ce besoin ?

### Amélioration UX de l'association de mots-clés.
En l'état, SPIP gère la liaison de mots-clés à un objet éditorial dans le mode VUE de cet objet.
Quelques tests utilisateurs montrent que cette dichotomie n'est pas toujours évidente pour un rédacteur occasionel·le.

Ce plugin permet de gèrer les liaisons avec des mots-clés directement dans le mode EDITION du formulaire.
Là, difficile de le rater.

Note : la gestion native des liaisons de mots-clés dans le mode VUE devient alors un raccourci pratique pour gérer (ajouter, supprimer) des liaisons de mot-clés sans avoir à passer par le mode EDITION.

### Utile pour un affichage publique de votre formulaire
Comme le dit la documentation de SPIP.net, il est possible d'utiliser dans les pages publiques les formulaires de l’espace privé : formulaire article, brève, etc. (voir https://www.spip.net/fr_article3788.html)

Mais si, par exemple, vous associez un groupe de mots-clés aux articles, il n'apparaitra pas dans l'édition de l'article dans la partie publique…

…sauf si vous utilisez Motsdf.

### Ok, mais il me semble qu'avec le plugin Champs Extras, je peux déjà faire tout cela…
C'est vrai. Pour ajouter par exemple une gestion de catégories à vos articles ou à vos brèves, c'est aussi la solution que j'ai utilisé pendant longtemps. La liste des catégories s'affiche bien dans le formulaire, privé et publique.

Mais choisir d'utiliser les mots-clés pour la gestion de catégories est plus pratique et plus évolutif.
La liste des avantages est longue, mais je retiendrai les points suivants :

- un responsable éditorial peut facilement modifier un groupe de mots clés. Alors que la modification d'un champ extra est plutôt réservé à des utilisateurs avertis;
- avec les mots-clés vous pouvez décider un jour de créer des associations entre objets éditoriaux;
- vous pouvez aussi joindre le logo du mot-clé, son descriptif, son texte;
- Vous pouvez ordonner par glisser/déposer vos mots-clés avec le plugin Rang;
- le tableau d'une liste de mots clés inclut des infos apréciées des responsables éditoriaux comme :
  - quels sont les types objets déjà liés;
  - pour chacun d'eux, le nombre de liaison;
- etc.

## Prérequis et configuration

### Prérequis
- SPIP >= 3.2
- Plugin SPIP Bonux (pris en compte automatiquement à l'installation)

### Configuration
*Motsdf* exploite les configurations qui se trouvent déjà nativement dans *Configuration -> Contenu du site*, paragraphe *Les mots-clés*.

- Vous devez avoir activé l'utilisation des mots-clés bien sûr.
- Vous pouvez aussi choisir d'*Utiliser la configuration avancée des groupes de mots-clés*. Ce choix permet d'exploiter d'autres options proposées par Motsdf (voir si dessous).

## Activation et options pour les rédacteurs
Tous ce qui suit se passe désormais dans la page de modification d'un groupe de mots, depuis *Édition -> Mots-clés*.

### Activer l'affichage d'un groupe de mots dans le formulaire d'un objet
Pour un groupe de mots-clés, vous devez cocher le(s) objet(s) auquel vous voulez l'associer.

### Option : Checkbox ou Boutons radio (par défaut 'Checkbox')
**Prérequis** : avoir coché•e la case "Utiliser la configuration avancée des groupes de mots-clés" (voir **Configuration -> Contenu du site**, bloc Les mots-clés).

Par défaut, le choix des mots clés se fait par Checkbox.
Si vous voulez des boutons radio, choisissez l'option "*On ne peut sélectionner qu’un seul mot-clé à la fois dans ce groupe.*"

### Option : Saisie obligatoire (par défaut 'saisie libre')
**Prérequis** : avoir coché•e la case "*Utiliser la configuration avancée des groupes de mots-clés*" (voir **Configuration -> Contenu du site**, bloc Les mots-clés).

Pour rendre la saisie obligatoire, choisissez l'option "*Groupe important : il est fortement conseillé de sélectionner un mot-clé dans ce groupe.*"

### Option : Restreindre l'affichage du groupe de mots-clés par rubrique (plugin Motus)(par défaut : 'aucune restriction')
Ce plugin est compatible avec le plugin Motus : Groupes de mots par rubrique.

Une fois activé, et toujours dans le formulaire d'édition du groupe de mots-clés, choisir les restrictions par rubrique qui vous conviennent.

Plus d'infos sur https://contrib.spip.net/Motus

## Options pour les Webmestres
Pour une composition, vous pouvez configurer quels seront les champs à afficher dans le formulaire de saisie du rédacteur.
Ainsi il est possible :
### d'activer la saisie d'un groupe de mots (voir plugin Motsdf)
Dans le fichier XML de votre composition vous devez alors ajouter la ligne suivante
```
<configuration>fieldset_mots:oui/id_groupe:4</configuration>
```

### d'activer le fiedset "bouton"
```
<configuration>fieldset_btn:oui</configuration>
```

## Compatibilité étendue
**Motsdf** est compatible avec le plugin **Rang** (https://contrib.spip.net/Rang-ordonner-une-liste-par-Drag-Drop).
Si vous ordonnez les mots-clés d'un groupe avec Rang, cela sera pris en compte dans l'affichage dans le formulaire.

Il est également compatible avec le plugin **Motsar** (https://contrib.spip.net/Mots-arborescents-4726)(Merci Nicod)

## Pipeline
Le pipeline **motsdf_activer_objet** permet de s'insérer dans le processus d'attribution d'un groupe de mots-clés à un objet éditorial.
Il devient par exemple possible d'insérer un groupe de mots-clés par un processus différent que celui proposé par le plugin lui-même. 
 
## API supplémentaire
Deux fonctions non liées directement à ce plugin sont présentes

### motsdf_liste_mots($id_groupe)
Retourner le tableau id_mot/titre d'un groupe de mots clés.

Très pratique lorsque l'on utilise le plugin <a href="https://contrib.spip.net/Saisies">Saisies</a>

Ex. : [(#SAISIE{checkbox, id_mot, label=Choisir une case à cocher, datas=[(#VAL{4}|motsdf_liste_mots)]})]
…permet d'afficher la saisie de la liste des mot-clés du groupe N°4 (id_groupe=4)

### objet_correspondance_association($objet, $id_objet, $type_objet_liens, $liaisons_demandees)
Traiter un lot de demandes de liaisons et/ou de dissociations. Bien utile pour les traitements par lot, par exemple un tableau de cases à cocher dans un formulaire.

## TODO

### Ecrire un script de migration d'un champs extras vers un groupe de mots-clés

### Option : quels status ?
Prise en compte des choix fait sur les statuts qui peuvent attribuer des mots-clés.
Attentio : voir le prérequis ci-dessous.

### Autoriser des visiteurs / tout le monde à attribuer les mots-clés
Cela peut être utile dans le cas d'un forum ouvert sur abonnement.

Si cela devient possible, alors utiliser les fonctions d'autorisations dans les tests fait dans le code (pipelines editer_contenu_objet() et formulaire_verifier())

### Cache Ajax dans le back-office
Pouvoir le shunter ! En effet si on supprime ou ajoute un mot-clé depuis la VUE, puis que l'on passe en mode EDITION, alors on ne voit pas les modifications. Il faut recharger la page pour voir les modifs apparaître.


