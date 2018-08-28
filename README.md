# Plugin motsdf pour SPIP <small>motsdf = Mots Dans Formulaires</small>

## Objectif
Ce plugin a pour vocation de répondre à un besoin UX lors de l’édition d’un objet éditorial.
Il permet, en quelques clics, de faire apparaitre la liste d'un groupe de mot-clés dans le mode ÉDITION d’un formulaire en particulier.
La liste des mots-clés peut prendre plusieurs formes au choix : checkbox ou boutons radio.


## Pourquoi ce besoin ?
La présence de la liste **dans** le mode EDITION du formulaire permet souvent aux éditeurs débutants de mieux comprendre ce qu'il faut saisir.
C'est important dans le cas d'une association comprenant plusieurs dizaines de membres de rendre les choses le plus intuitif possible.

D'un point de vue éditorial, cet ajout est complémentaire du composant de sélection de mots-clés du mode VUE d'un objet éditorial. Ce dernier devenant alors un raccourci bien pratique pour changer le contenu de la liste du ou des mots clés.


## API supplémentaire
Deux fonctions non liées directement à ce plugin sont présentes

**function motsdf_liste_mots($id_groupe)**
Retourner le tableau id_mot/titre d'un groupe de mots clés
Très pratique lorsque l'on utilise le plugin <a href="https://contrib.spip.net/Saisies">Saisies</a>
Ex. : [(#SAISIE{checkbox, id_mot, label=Choisir une case à cocher, datas=[(#VAL{4}|motsdf_liste_mots)]})]

**function objet_correspondance_association($objet, $id_objet, $type_objet_liens, $liaisons_demandees)**
Traiter un lot de demandes de liaisons et/ou de dissociations. Bien utile pour les traitements par lot, par exemple un tableau de cases à cocher dans un formulaire.