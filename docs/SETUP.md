# Setup Guide

## Prerequisites

- PHP 8.0+
- MySQL 8+ (or compatible MariaDB)
- Composer

## 1) Clone and Install

```bash
git clone <your-repo-url>
cd ticket-reservation-system
composer install
composer dump-autoload
```

## 2) Configure Environment

```bash
cp .env.example .env
```

Update `.env` values for your local DB.

## 3) Create Database Schema

```bash
mysql -u <db_user> -p < examples/schema.sql
```

The schema is a starter version inferred from this codebase. Adapt columns/constraints if your existing project schema differs.

## 4) Validate PHP Files

```bash
composer run lint
```

## 5) Run from Your Integration Layer

This repository does not include an HTTP router/app entrypoint.
Use the classes in `src/` from your controller layer or framework routes.

A minimal class usage example is provided in [examples/quick_start.php](../examples/quick_start.php).
