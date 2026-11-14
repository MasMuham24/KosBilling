# KosBilling

A web-based boarding house management and billing system built with Laravel. Designed to simplify property management, tenant administration, monthly billing, and payment tracking through a centralized dashboard.

## Overview

KosBilling provides a centralized platform for boarding house owners to manage their rental operations. It streamlines daily administrative tasks, from managing properties and tenants to generating monthly invoices and monitoring payment transactions.

The application integrates Midtrans for online payments and a WhatsApp Gateway for billing notifications.

## Features

- **Dashboard** — Overview of properties, rooms, tenants, and billing statistics.
- **Property Management** — Manage boarding house properties and their information.
- **Room Management** — Organize rooms, rental prices, and availability.
- **Tenant Management** — Manage tenant records, room assignments, and rental periods.
- **Billing Management** — Generate and manage monthly rental invoices.
- **Payment Management** — Track payment statuses and transaction history.
- **Payment Gateway** — Integrated with Midtrans for online transactions.
- **WhatsApp Notifications** — Send billing reminders and payment notifications.
- **Digital Invoices** — Provide accessible invoice pages for tenants.

## Tech Stack

| Technology | Description |
|---|---|
| PHP | Backend language |
| Laravel | Backend framework |
| MySQL | Relational database |
| Blade | Server-side templating |
| Tailwind CSS | UI styling |
| Vite | Frontend build tool |
| Midtrans | Payment gateway |
| Node.js | WhatsApp gateway runtime |
| Baileys | WhatsApp integration |

## Requirements

- PHP 8.2+
- Composer
- Node.js and npm
- MySQL
- Git

## Installation

**1. Clone the repository**

```bash
git clone https://github.com/MasMuham24/KosBilling.git
cd KosBilling
```

**2. Install dependencies**

```bash
composer install
npm install
```

**3. Configure environment**

```bash
cp .env.example .env
php artisan key:generate
```

Configure your database credentials and integration settings in `.env`.

**4. Run migrations**

```bash
php artisan migrate
```

**5. Build frontend assets**

```bash
npm run build
```

**6. Start the application**

```bash
php artisan serve
```

Access the application at `http://127.0.0.1:8000`.

## WhatsApp Gateway

The WhatsApp Gateway runs separately from the Laravel application.

```bash
cd gateway
npm install
node server.js
```

Configure the gateway connection in the Laravel environment file.

## Payment Integration

The application uses Midtrans Snap for online payment processing.

Configure the required Midtrans credentials and notification endpoint before enabling payment transactions.

## Project Purpose

KosBilling was developed to provide a practical solution for managing boarding house rental operations, reducing manual administrative work, and improving payment management.

## Developer

**Muhammad Syafi'i**

GitHub: [@MasMuham24](https://github.com/MasMuham24)

