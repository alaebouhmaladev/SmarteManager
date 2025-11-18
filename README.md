# Smarte Manager  
### HR, Attendance, Inventory & Expense Management System  
**Laravel API + Vue.js SPA + MySQL**

---

## 🧩 Description du Projet

Smarte Manager est une application web full-stack conçue pour les restaurants, cafés, fast-foods, foodtrucks, pâtisseries, et toute petite ou moyenne entreprise.

Elle permet de gérer :

- Les employés  
- Le pointage (heures d’entrée et sortie)  
- Le calcul automatique des heures travaillées  
- La génération de salaires  
- L’inventaire et les produits  
- Les mouvements de stock (entrées/sorties)  
- Les dépenses par catégorie  
- Les fournisseurs  
- Un tableau de bord complet

Le backend est développé en **Laravel** (API REST), le frontend en **Vue.js**, et la base de données en **MySQL**.

---

## 🚀 Fonctionnalités Principales

### 🧑‍💼 Module RH & Pointage
- CRUD employés  
- Check-In / Check-Out  
- Calcul automatique des heures travaillées  
- Historique quotidien & mensuel  
- Calcul des salaires mensuels  
- Export des données (future feature)

---

### 📦 Module Inventaire
- Gestion des produits / ingrédients  
- Stock en temps réel  
- Entrées / Sorties du stock  
- Calcul du coût moyen  
- Alertes de stock minimum  
- Valeur totale du stock

---

### 💰 Module Dépenses
- Dépenses classées par catégorie  
- Montant, date, fournisseur  
- Rapport mensuel  
- Gestion des catégories (optionnel)

---

### 🏪 Module Fournisseurs
- Gestion des fournisseurs  
- Historique des achats et dépenses  
- Informations complètes : nom, contact, téléphone, etc.

---

## 🛠️ Technologies Utilisées

### 🔧 Backend (Laravel API)
- Laravel 10  
- Laravel Sanctum (token-based auth)  
- API Resources  
- Middlewares  
- Validation Requests  
- Migrations & Seeders  

### 🎨 Frontend (Vue.js 3)
- Vue Router  
- Vue 3 Composition API  
- Pinia (state management)  
- Axios (HTTP requests)  
- TailwindCSS / Bootstrap  
- Component-based UI  

### 🗄 Base de données
- MySQL  
- Relations One-to-Many  
- Relations Many-to-One  
- Index optimisés  

---

## 🧱 Architecture du Projet

### Schéma Global
```
Vue.js (Frontend SPA)
      ⇩ Axios
Laravel API (Backend REST)
      ⇩
   MySQL DB
```

### Structure Backend
```
app/
  Http/
    Controllers/Api/
    Middleware/
  Models/
routes/
  api.php
database/
  migrations/
```

### Structure Frontend
```
src/
  api/
  components/
  layouts/
  router/
  store/
  views/
```

---

## 📡 API Endpoints

### 🔐 Auth
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/login` | Connexion |
| GET | `/api/me` | Profil utilisateur |
| POST | `/api/logout` | Déconnexion |

### 🧑‍💼 Employees
| Method | Endpoint |
|--------|----------|
| GET | `/api/employees` |
| POST | `/api/employees` |
| PUT | `/api/employees/{id}` |
| DELETE | `/api/employees/{id}` |

### ⏱ Attendance
| Method | Endpoint |
|--------|----------|
| POST | `/api/attendances/check-in` |
| POST | `/api/attendances/check-out` |

### 🏪 Inventory
| Method | Endpoint |
|--------|----------|
| GET | `/api/products` |
| POST | `/api/products` |
| POST | `/api/stock-movements` |

### 💸 Expenses
| Method | Endpoint |
|--------|----------|
| GET | `/api/expenses` |
| POST | `/api/expenses` |

---

## 🧪 Installation & Setup

### Backend — Laravel
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

### Frontend — Vue.js
```bash
npm install
npm run dev
```

---

## 🎯 Objectifs du Projet
- Centraliser la gestion RH et Inventaire  
- Automatiser les tâches répétitives  
- Offrir un outil rapide et moderne pour PME  
- Avoir une application scalable et réutilisable  

---

## 📌 Améliorations Futures
- Export PDF  
- Module CRM  
- Système de commandes clients  
- Multi-magasin  
- Application mobile  

---

## 👤 Réalisé par
**Nabil**  
Projet Fin d’Année – Smarte Manager  
Encadrant : *À compléter*

---
