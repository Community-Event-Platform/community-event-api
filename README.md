# EventHub - Community Event Platform API

## Overview

EventHub API is the backend service of the Community Event Platform, developed using Laravel 12 and MySQL. It provides secure authentication, event management, registration workflows, waitlist automation, review management, and notification services for both attendees and organizers.

This repository serves as the central RESTful API for:

* Attendee Client Application
* Organizer Dashboard Application

---

## Features

### Authentication & Authorization

* User Registration
* User Login
* Google OAuth Login
* Laravel Sanctum Authentication
* Role-Based Access Control (RBAC)
* Protected API Routes

### Event Management

* Create Events
* Update Events
* Delete Events
* Publish/Draft Events
* Event Categories
* Event Image Upload

### Registration Management

* Event Registration
* Registration Approval/Rejection
* Registration Cancellation
* Automatic Waitlist Handling
* Capacity Tracking

### Custom Registration Forms

* Dynamic Form Builder
* Custom Questions
* Form Response Storage
* Additional Attendee Information Collection

### Reviews & Ratings

* Submit Reviews
* Event Ratings
* Review Display

### Notification System

* In-App Notifications
* Email Notifications
* Registration Status Updates
* Waitlist Promotion Notifications

---

## Tech Stack

| Technology      | Purpose               |
| --------------- | --------------------- |
| Laravel 12      | Backend Framework     |
| PHP 8.2+        | Programming Language  |
| MySQL           | Database              |
| Laravel Sanctum | Authentication        |
| Firebase JWT    | Token Management      |
| Laravel Mail    | Email Notifications   |
| Composer        | Dependency Management |

---

## Architecture

The project follows the MVC Architecture pattern.

```text
app/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│
├── Models/
│
├── Mail/
│
├── Providers/
│
database/
├── migrations/
├── seeders/
│
routes/
├── api.php
```

---

## Database Entities

* Users
* Events
* Registrations
* Form Responses
* Notifications
* Reviews
* Categories

---

## Main API Endpoints

### Public Routes

```http
GET    /api/events
GET    /api/events/{id}
GET    /api/events/search
POST   /api/login
```

### Protected Routes

```http
POST   /api/events
POST   /api/events/{id}/register
POST   /api/events/{id}/register/paid

PATCH  /api/registrations/{id}/approve
PATCH  /api/registrations/{id}/cancel
```

### Payment Routes

```http
GET /api/payment/vnpay/return
```

---

## Installation

### Clone Repository

```bash
git clone https://github.com/Community-Event-Platform/community-event-api.git

cd community-event-api
```

### Install Dependencies

```bash
composer install
```

### Configure Environment

```bash
cp .env.example .env
```

Update database configuration:

```env
DB_DATABASE=eventhub
DB_USERNAME=root
DB_PASSWORD=
```

### Generate Application Key

```bash
php artisan key:generate
```

### Run Migrations

```bash
php artisan migrate
```

### Create Storage Link

```bash
php artisan storage:link
```

### Start Development Server

```bash
php artisan serve
```

---

## Waitlist Automation

When an event reaches its maximum capacity:

1. New attendees are automatically added to the waitlist.
2. Queue positions are assigned using FIFO logic.
3. If a participant cancels:

   * The first user in the waitlist is promoted automatically.
   * Email notification is sent.
   * Queue positions are updated automatically.

---

## Team

### Group 5 – Advanced Web Application Development

* Nguyễn Thị Dung
* Nguyễn Tiến Nhựt
* Hồ Thị Vãi
* Hồ Văn Tiết

Passerelles Numériques Vietnam (PNV)
