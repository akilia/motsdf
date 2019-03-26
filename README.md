# Plugin Motsdf pour SPIP
**motsdf** = Mots Dans Formulaires

## Objectif
Afficher les mots-clés d'un groupe dans le mode ÉDITION d’un formulaire d'objet éditorial.


## Pourquoi ce besoin ?

### Amélioration UX du Back-office
En l'état, SPIP gère la liaison de mots-clés à un objet éditorial dans le mode VUE de cet objet.
Quelques tests utilisateurs montrent que cette dichotomie n'est pas toujours évidente pour un rédacteur occasionel.

Ce plugin propose de gèrer la liaison de mots-clés directement dans le mode EDITION du formulaire. Là, difficile de le rater.
La gestion native des liaisons de mots-clés dans le mode VUE devient alors un raccourci pratique pour modifier (ajouter, supprimer) des liaisons de mot-clés (i.e. sans avoir à passer par le mode EDITION).

### Utile pour un affichage publique de votre formulaire
Comme le dit la documentation de SPIP.net, il est possible d'utiliser dans les pages publiques les formulaires de l’espace privé : formulaire article, breve, etc. (voir https://www.spip.net/fr_article3788.html)
Mais si vous liez un groupe de mots-clés aux articles, il n'apparaitra pas dans le formulaire article dans la partie publique.
Sauf si vous utilisez Motsdf.

## Prérequis et Configuration

### Prérequis
SPIP >= 3.2

Plugin Saisies (pris en compte automatiquement à l'installation)

### Configuration
*Motsdf* exploite uniquement les options de configuration qui se trouvent déjà nativement dans le formulaire d'édition d'un groupe de mots-clés. Voir *Édition -> Mots-clés*

#### Afficher le groupe de mots dans le formulaire
Lors de la création d'un nouveau groupe de mots-clés, dans le champ "Les mots-clés de ce groupe peuvent être associés :", cocher la case correspondant à l'objet éditorial dans lequel vous voulez voir apparaitre ce groupe. 

#### Checkbox ou Boutons radio
**Prérequis** : avoir coché•e la case "Utiliser la configuration avancée des groupes de mots-clés" (voir **Configuration -> Contenu du site**, bloc Les mots-clés).

Par défaut, le choix des mots clés se fait par Checkbox.
Si vous voulez des boutons radio, choisissez l'option "*On ne peut sélectionner qu’un seul mot-clé à la fois dans ce groupe.*"

#### Saisie obligatoire
**Prérequis** : avoir coché•e la case "*Utiliser la configuration avancée des groupes de mots-clés*" (voir **Configuration -> Contenu du site**, bloc Les mots-clés).

Pour rendre la saisie obligatoire, choisissez l'option "*Groupe important : il est fortement conseillé de sélectionner un mot-clé dans ce groupe.*"


#### Restreindre l'affichage du groupe de mots-clés par rubrique (plugin Motus)
Ce plugin est compatible avec le plugin Motus : Groupes de mots par rubrique. Et ce plugin Motus, je ne saurais trop vous le recommander ! 

Une fois activé, et toujours dans le formulaire d'édition du groupe de mots-clés, choisir les restrictions par rubrique qui vous conviennent.

Plus d'infos sur https://contrib.spip.net/Motus

## Compatibilité étendue
Motsdf est compatible avec le plugin RANG (https://contrib.spip.net/Rang-ordonner-une-liste-par-Drag-Drop).
Si vous ordonnez les mots-clés d'un groupe avec Rang, cela sera pris en compte dans l'affichage dans le formulaire.

## API supplémentaire
Deux fonctions non liées directement à ce plugin sont présentes

### function motsdf_liste_mots($id_groupe)
Retourner le tableau id_mot/titre d'un groupe de mots clés.

Très pratique lorsque l'on utilise le plugin <a href="https://contrib.spip.net/Saisies">Saisies</a>

Ex. : [(#SAISIE{checkbox, id_mot, label=Choisir une case à cocher, datas=[(#VAL{4}|motsdf_liste_mots)]})]
…permet d'afficher la saisie de la liste des mot-clés du groupe N°4 (id_groupe=4)

### function objet_correspondance_association($objet, $id_objet, $type_objet_liens, $liaisons_demandees)
Traiter un lot de demandes de liaisons et/ou de dissociations. Bien utile pour les traitements par lot, par exemple un tableau de cases à cocher dans un formulaire.

## TODO
Trouver une solution pour shunter l'affichage dans le formulaire pour des cas particuliers.
