# symfony-user-api

API REST complète construite avec **Symfony 8** et **API Platform v4**, conçue comme projet d'apprentissage PHP pour développeurs Java.

---

## 🎯 Ce que fait ce projet

Une API CRUD de gestion d'utilisateurs et d'articles avec :
- Gestion des **Users** (créer, lire, mettre à jour, désactiver, supprimer)
- Gestion des **Articles** liés à un User (relation ManyToOne)
- Double exposition : **API Platform** (JSON-LD) + **Controller classique** (JSON)
- Interface HTML **Twig** pour visualiser les données

---

## 🏗️ Architecture

```
Request → Nginx → PHP-FPM → Symfony
                              ├── Controller (routes HTTP)
                              ├── State Provider/Processor (API Platform)
                              ├── Service (logique métier + validation)
                              ├── Repository (accès base de données)
                              └── Entity (modèle de données Doctrine)
```

### Couches applicatives

| Couche | Rôle | Équivalent Java |
|---|---|---|
| `Entity` | Modèle de données + mapping BDD | `@Entity` JPA |
| `Repository` | Accès base de données | `JpaRepository` |
| `Service` | Logique métier + validation | `@Service` Spring |
| `Controller` | Routes HTTP classiques | `@RestController` Spring |
| `State Provider` | Lecture via API Platform | `@GetMapping` |
| `State Processor` | Écriture via API Platform | `@PostMapping` |

---

## 🛣️ Endpoints disponibles

### API Platform (JSON-LD)
| Méthode | URL | Description |
|---|---|---|
| `GET` | `/api/users` | Liste tous les users |
| `GET` | `/api/users/{id}` | Récupère un user |
| `POST` | `/api/users` | Crée un user |
| `PUT` | `/api/users/{id}` | Met à jour un user |
| `PATCH` | `/api/users/{id}/deactivate` | Désactive un user |
| `DELETE` | `/api/users/{id}` | Supprime un user |
| `GET` | `/api/articles/{id}` | Récupère un article |
| `GET` | `/api/users/{id}/articles` | Articles d'un user |
| `POST` | `/api/users/{id}/articles` | Crée un article pour un user |

### Controller classique (JSON)
| Méthode | URL | Description |
|---|---|---|
| `GET` | `/api/custom/users` | Liste tous les users (HTML Twig) |
| `GET` | `/api/custom/users/{id}` | Détail d'un user (HTML Twig) |
| `GET` | `/api/custom/articles/{id}` | Récupère un article |
| `GET` | `/api/custom/users/{id}/articles` | Articles d'un user |
| `POST` | `/api/custom/users/{id}/articles` | Crée un article |

### Documentation Swagger
```
GET /api
```

---

## 🧰 Stack technique

- **PHP** 8.4+
- **Symfony** 8.0
- **API Platform** v4.2
- **Doctrine ORM** (MySQL 8.0)
- **PHPUnit** 13
- **Docker** (PHP-FPM + Nginx + MySQL)
- **Composer** 2

---

## 🚀 Installation locale (sans Docker)

### Prérequis
- PHP 8.4+
- Composer 2
- MySQL 8.0 (WAMP, MAMP, Laragon...)

### Étapes

```bash
# 1. Cloner le projet
git clone https://github.com/abenabbes/symfony-user-api.git
cd symfony-user-api

# 2. Installer les dépendances
composer install

# 3. Configurer la base de données
# Copier et adapter le fichier d'environnement
cp .env .env.local
# Éditer .env.local et modifier DATABASE_URL :
# DATABASE_URL="mysql://root:@127.0.0.1:3306/symfony_user_api?serverVersion=8.0.32&charset=utf8mb4"

# 4. Créer la base de données
php bin/console doctrine:database:create

# 5. Exécuter les migrations
php bin/console doctrine:migrations:migrate --no-interaction

# 6. Lancer le serveur
php -S localhost:8000 -t public/
```

L'API est disponible sur `http://localhost:8000/api`

---

## 🐳 Installation avec Docker

### Prérequis
- Docker Desktop

### Étapes

```bash
# 1. Cloner le projet
git clone https://github.com/abenabbes/symfony-user-api.git
cd symfony-user-api

# 2. Builder et démarrer les containers
docker-compose up --build

# 3. Dans un second terminal, installer les dépendances PHP
docker exec -it <nom_container_php> composer install --no-interaction

# 4. Exécuter les migrations
docker exec -it <nom_container_php> php bin/console doctrine:migrations:migrate --no-interaction
```

> Pour trouver le nom exact du container PHP : `docker ps`

### Accès
| Service | URL |
|---|---|
| API | `http://localhost:8080/api` |
| Swagger UI | `http://localhost:8080/api` |
| Interface Twig | `http://localhost:8080/api/custom/users` |
| MySQL (externe) | `localhost:3307` |

### Architecture Docker
```
docker-compose
├── php      → PHP 8.4-FPM (port 9000 interne)
├── nginx    → Nginx Alpine (port 8080 → 80)
└── mysql    → MySQL 8.0 (port 3307 → 3306)
```

### Commandes Docker utiles
```bash
# Démarrer les containers
docker-compose up

# Arrêter les containers
docker-compose down

# Reconstruire les images
docker-compose up --build

# Accéder au container PHP
docker exec -it <nom_container_php> bash

# Vider le cache Symfony dans Docker
docker exec -it <nom_container_php> php bin/console cache:clear
```

---

## 🧪 Tests

### Configuration
Les tests utilisent une base de données séparée `symfony_user_api_test`.

```bash
# Créer la base de test
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --env=test --no-interaction
```

### Lancer les tests

```bash
# Tous les tests
php bin/phpunit

# Tests unitaires uniquement
php bin/phpunit tests/Service/UserServiceTest.php

# Tests d'intégration uniquement
php bin/phpunit tests/Repository/UserRepositoryTest.php

# Tests fonctionnels uniquement
php bin/phpunit tests/Controller/UserControllerTest.php
```

### Types de tests
| Type | Fichier | Description |
|---|---|---|
| Unitaires | `UserServiceTest` | Logique métier avec Mocks |
| Intégration | `UserRepositoryTest` | Repository avec vraie BDD |
| Fonctionnels | `UserControllerTest` | Endpoints HTTP bout en bout |

---

## 📁 Structure du projet

```
symfony-user-api/
├── config/
│   ├── packages/api_platform.yaml   # Configuration API Platform
│   └── services.yaml                # Liaison interfaces/implémentations
├── docker/
│   ├── nginx/default.conf           # Configuration Nginx
│   └── php/Dockerfile               # Image PHP-FPM
├── docker-compose.yml               # Orchestration Docker
├── migrations/                      # Migrations Doctrine
├── src/
│   ├── Controller/                  # Controllers HTTP
│   ├── Entity/                      # Entités Doctrine (User, Article, Address)
│   ├── Exception/                   # Exceptions métier
│   ├── Repository/                  # Accès base de données + interfaces
│   ├── Service/                     # Logique métier
│   └── State/                       # API Platform Provider/Processor
├── templates/                       # Vues Twig
└── tests/                           # Tests PHPUnit
    ├── Controller/
    ├── Repository/
    └── Service/
```

---

## 🔧 Commandes utiles

```bash
# Vider le cache
php bin/console cache:clear

# Voir toutes les routes
php bin/console debug:router

# Générer une migration après modification d'entité
php bin/console doctrine:migrations:diff

# Exécuter les migrations
php bin/console doctrine:migrations:migrate
```

---

## 📝 Format des requêtes API Platform

API Platform v4 utilise le format **JSON-LD** par défaut.  
Le header `Content-Type: application/ld+json` est obligatoire pour les requêtes POST/PUT/PATCH.

```bash
# Exemple POST
curl -X POST http://localhost:8080/api/users \
  -H "Content-Type: application/ld+json" \
  -d '{"name": "Alice", "age": 30, "email": "alice@example.com"}'
```
