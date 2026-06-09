# Projet TROC

## Présentation

Projet TROC est une plateforme web permettant à des utilisateurs de proposer, demander et échanger des objets ou des services.  
Chaque utilisateur peut publier des objets, consulter les objets disponibles, faire des propositions d’échange ou proposer une somme en monnaie interne.

Ce projet repose sur un modèle de données composé principalement des entités suivantes :

- `Utilisateur`
- `Objet`
- `Categorie`
- `Proposition`

Le MCD fourni sert de base pour construire le MLD, créer la base MySQL, insérer des données de test et préparer les requêtes SQL nécessaires aux fonctionnalités du site.

---

## Objectifs du projet

Le projet a pour objectif de créer une application de troc fonctionnelle permettant de :

- gérer des utilisateurs ;
- publier des objets ou services ;
- classer les objets par catégories ;
- gérer des sous-catégories ;
- faire des propositions d’échange ;
- accepter, refuser ou laisser une proposition en attente ;
- suivre la disponibilité des objets.

---

## Fonctionnalités prévues

### Utilisateurs

Un utilisateur peut :

- créer un compte ;
- modifier son profil ;
- publier un objet ;
- faire une proposition sur un objet ;
- consulter ses objets publiés ;
- consulter les propositions reçues ou envoyées.

### Objets

Un objet contient :

- un nom ;
- une image ;
- une description ;
- un prix ;
- un état de disponibilité ;
- une catégorie ;
- un créateur.

Un objet appartient obligatoirement à une catégorie et est créé par un seul utilisateur.

### Catégories

Les catégories permettent d’organiser les objets ou services.

Une catégorie peut :

- contenir plusieurs objets ;
- être une catégorie principale ;
- être une sous-catégorie d’une autre catégorie ;
- représenter un objet ou un service.

### Propositions

Une proposition représente une demande faite par un utilisateur.

Elle contient :

- un prix proposé ;
- un état : `VALIDE`, `REFUSE` ou `ATTENTE` ;
- l’utilisateur qui propose ;
- l’objet demandé ;
- éventuellement un objet offert en échange.

Une proposition peut donc être :

- une offre monétaire ;
- un échange contre un autre objet ;
- une combinaison des deux selon l’évolution du projet.

---

## Modèle Conceptuel de Données

Le MCD du projet contient quatre entités principales.

### Utilisateur

| Champ | Type | Description |
|---|---|---|
| id | INT, clé primaire | Identifiant unique de l’utilisateur |
| pseudo | VARCHAR(25) | Nom affiché de l’utilisateur |
| mail | VARCHAR(25) | Adresse mail |
| miel | DECIMAL(8,2) | Monnaie interne ou solde utilisateur |
| image_profil | VARCHAR(256) | Image de profil |
| ville | VARCHAR(50) | Ville de l’utilisateur |
| region | ENUM | Région de l’utilisateur |
| description | TEXT | Description du profil |

### Objet

| Champ | Type | Description |
|---|---|---|
| id | INT, clé primaire | Identifiant unique de l’objet |
| nom | VARCHAR(25) | Nom de l’objet |
| image | VARCHAR(256) | Image de l’objet |
| description | TEXT | Description de l’objet |
| prix | DECIMAL(8,2) | Prix ou valeur estimée |
| estDispo | INT(1) | Disponibilité de l’objet |

### Categorie

| Champ | Type | Description |
|---|---|---|
| nom | VARCHAR(25), clé primaire | Nom de la catégorie |
| estUnService | INT(1) | Indique si la catégorie concerne un service |

### Proposition

| Champ | Type | Description |
|---|---|---|
| id | INT, clé primaire | Identifiant unique de la proposition |
| prix | DECIMAL(8,2) | Prix proposé |
| etat_prop | ENUM | État de la proposition : `VALIDE`, `REFUSE`, `ATTENTE` |

---

## Relations principales

### Utilisateur — Objet

Un utilisateur peut créer plusieurs objets.  
Un objet est créé par un seul utilisateur.

Relation :

```text
Utilisateur 0,n --- Créer --- 1,1 Objet
```

### Objet — Categorie

Une catégorie peut contenir plusieurs objets.  
Un objet appartient à une seule catégorie.

Relation :

```text
Categorie 0,n --- Appartient à --- 1,1 Objet
```

### Categorie — Categorie

Une catégorie peut être incluse dans une autre catégorie.  
Cela permet de gérer des catégories principales et des sous-catégories.

Relation :

```text
Categorie parent 0,n --- Est incluse dans --- 0,1 Categorie enfant
```

### Utilisateur — Proposition

Un utilisateur peut faire plusieurs propositions.  
Une proposition est faite par un seul utilisateur.

Relation :

```text
Utilisateur 0,n --- Proposer --- 1,1 Proposition
```

### Proposition — Objet demandé

Une proposition demande un seul objet.  
Un objet peut recevoir plusieurs propositions.

Relation :

```text
Proposition 1,1 --- Demande --- 0,n Objet
```

### Proposition — Objet offert

Une proposition peut offrir un objet en échange.  
Cette partie est facultative, car une proposition peut aussi être uniquement monétaire.

Relation :

```text
Proposition 0,1 --- Offre --- 0,n Objet
```

---

## Modèle Logique de Données proposé

Une traduction possible du MCD en MLD est la suivante :

```text
Utilisateur(
    id,
    pseudo,
    mail,
    miel,
    image_profil,
    ville,
    region,
    description
)

Categorie(
    nom,
    estUnService,
    cat_parent
)

Objet(
    id,
    nom,
    image,
    description,
    prix,
    estDispo,
    id_utilisateur,
    nom_categorie
)

Proposition(
    id,
    prix,
    etat_prop,
    id_utilisateur,
    id_objet_demande,
    id_objet_offert
)
```

### Clés étrangères

```text
Objet.id_utilisateur → Utilisateur.id
Objet.nom_categorie → Categorie.nom

Categorie.cat_parent → Categorie.nom

Proposition.id_utilisateur → Utilisateur.id
Proposition.id_objet_demande → Objet.id
Proposition.id_objet_offert → Objet.id
```

---

## Structure SQL attendue

Le projet devra contenir :

- un script de création de la base de données ;
- les tables correspondant au MLD ;
- les clés primaires ;
- les clés étrangères ;
- des contraintes adaptées ;
- des données de test ;
- les requêtes SQL nécessaires aux fonctionnalités principales.

Exemples de requêtes à prévoir :

- afficher tous les objets disponibles ;
- afficher les objets d’un utilisateur ;
- afficher les objets d’une catégorie ;
- créer une proposition ;
- afficher les propositions reçues pour un objet ;
- accepter ou refuser une proposition ;
- afficher les sous-catégories d’une catégorie.

---

## Exemple de script MySQL à produire

Le projet devra contenir un fichier SQL du type :

```sql
CREATE DATABASE IF NOT EXISTS projet_troc;
USE projet_troc;

CREATE TABLE Utilisateur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pseudo VARCHAR(25) NOT NULL,
    mail VARCHAR(25) NOT NULL UNIQUE,
    miel DECIMAL(8,2) DEFAULT 0,
    image_profil VARCHAR(256),
    ville VARCHAR(50),
    region VARCHAR(50),
    description TEXT
);

CREATE TABLE Categorie (
    nom VARCHAR(25) PRIMARY KEY,
    estUnService INT(1) NOT NULL DEFAULT 0,
    cat_parent VARCHAR(25),
    FOREIGN KEY (cat_parent) REFERENCES Categorie(nom)
);

CREATE TABLE Objet (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(25) NOT NULL,
    image VARCHAR(256),
    description TEXT,
    prix DECIMAL(8,2) DEFAULT 0,
    estDispo INT(1) DEFAULT 1,
    id_utilisateur INT NOT NULL,
    nom_categorie VARCHAR(25) NOT NULL,
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id),
    FOREIGN KEY (nom_categorie) REFERENCES Categorie(nom)
);

CREATE TABLE Proposition (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prix DECIMAL(8,2) DEFAULT 0,
    etat_prop ENUM('VALIDE', 'REFUSE', 'ATTENTE') DEFAULT 'ATTENTE',
    id_utilisateur INT NOT NULL,
    id_objet_demande INT NOT NULL,
    id_objet_offert INT,
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id),
    FOREIGN KEY (id_objet_demande) REFERENCES Objet(id),
    FOREIGN KEY (id_objet_offert) REFERENCES Objet(id)
);
```

---

## Organisation possible du projet

```text
projet-troc/
│
├── README.md
├── index.php
├── connexion.php
├── css/
│   └── style.css
├── sql/
│   ├── creation_base.sql
│   ├── insertion_donnees.sql
│   └── requetes.sql
├── pages/
│   ├── objets.php
│   ├── profil.php
│   ├── proposition.php
│   └── categories.php
└── images/
    ├── objets/
    └── profils/
```

---

## Technologies utilisées

Le projet peut être réalisé avec :

- HTML ;
- CSS ;
- PHP ;
- MySQL ;
- JavaScript si nécessaire.

---

## Installation locale

### 1. Cloner le projet

```bash
git clone <url-du-repository>
```

### 2. Placer le projet dans le dossier serveur

Avec MAMP, placer le dossier dans :

```text
C:/MAMP/htdocs/
```

### 3. Créer la base de données

Lancer MAMP, ouvrir phpMyAdmin, puis exécuter le script SQL de création de base.

### 4. Configurer la connexion à la base

Dans le fichier `connexion.php`, adapter les paramètres :

```php
$host = "localhost";
$dbname = "projet_troc";
$user = "root";
$password = "root";
```

Selon la configuration locale, le mot de passe peut être vide ou différent.

### 5. Lancer le site

Dans le navigateur :

```text
http://localhost/projet-troc/
```

---

## État d’avancement

- [ ] Création du MCD
- [ ] Traduction du MCD en MLD
- [ ] Création du script MySQL
- [ ] Insertion des premières données
- [ ] Création des pages principales
- [ ] Gestion des utilisateurs
- [ ] Gestion des objets
- [ ] Gestion des catégories
- [ ] Gestion des propositions
- [ ] Tests des requêtes SQL
- [ ] Finalisation du rendu

---

## Auteurs

Projet réalisé dans le cadre du projet TROC.

Membres du groupe :

- Adrien Rochette
- À compléter
- À compléter
- À compléter

---

## Remarque

Ce README est une base de travail.  
Il peut être modifié selon les choix techniques, les fonctionnalités ajoutées et l’évolution du MCD.
