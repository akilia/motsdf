# Plugin motsdf pour SPIP
motsdf = Mots Dans Formulaires

## Objectif
Permettre en quelques clics de faire apparaitre les mot-clés d'un groupe (de mots-clés) dans le mode ÉDITION d’un formulaire en particulier.
L'affichage de cette liste des mots-clés peut prendre deux formes au choix : checkbox ou boutons radio.


## Pourquoi ce besoin ?
En l'état, SPIP n'affiche un groupe de mots-clés que dans le mode VUE d'un objet éditorial.
Après quelques tests avec des utilisateurs, je me suis rendu compte que parfois (souvent ?), la saisie de ces mots-clés n'était pas prise en compte par le rédacteur.

Ce plugin propose la solution suivante : afficher la liste **dans** le mode EDITION du formulaire. Là, impossible de le rater

Le composant natif de SPIP de sélection de mots-clés du mode VUE d'un objet éditorial devient alors un raccourci pratique pour 
* voir rapidement le/les mots clés associés,
* les modifier (ajouter, enlever)

## Configuration
### Afficher le groupe de mots dans le formulaire
Lors de la création d'un nouveau groupe de mots-clés, dans le champ "Les mots-clés de ce groupe peuvent être associés :", cocher la case correspondant à l'objet éditorial dans lequel vous voulez voir apparaitre ce groupe. 

### Checkbox ou boutons radio
Par défaut, le choix des mots clés se fait par Checkbox.
Si vous voulez des boutons radio,  vous devez activer "la configuration avancée des groupes de mots-clés" (voir **Configuration -> Contenu du site**, bloc Les mots-clés), puis dans le formulaire de configuration de groupe de mots-clés, cliquez sur l'option "On ne peut sélectionner qu’un seul mot-clé à la fois dans ce groupe."


## API supplémentaire
Deux fonctions non liées directement à ce plugin sont présentes

### function motsdf_liste_mots($id_groupe)
Retourner le tableau id_mot/titre d'un groupe de mots clés
Très pratique lorsque l'on utilise le plugin <a href="https://contrib.spip.net/Saisies">Saisies</a>

Ex. : [(#SAISIE{checkbox, id_mot, label=Choisir une case à cocher, datas=[(#VAL{4}|motsdf_liste_mots)]})]

### function objet_correspondance_association($objet, $id_objet, $type_objet_liens, $liaisons_demandees)
Traiter un lot de demandes de liaisons et/ou de dissociations. Bien utile pour les traitements par lot, par exemple un tableau de cases à cocher dans un formulaire.

## TODO's
rendre compatible ce plugin avec Motus (et autres ?)