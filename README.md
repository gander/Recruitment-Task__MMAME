# Test Environment Setup Guide

This document explains how to set up and run the test environment for this Symfony 7.3.0 application.

## Prerequisites

- Docker and Docker Compose installed on your machine
- Git to clone the repository

## Setup Instructions

### 1. Install dependencies

To install PHP dependencies, run Composer in a one-off FrankenPHP container:

```bash
docker compose run --rm frankenphp composer install
```

### 2. Start the Docker environment

The project uses Docker Compose to run MySQL and FrankenPHP. To start the environment, run the following command in the project root directory:

```bash
docker compose up -d
```

This will start two containers:
- FrankenPHP (web server with PHP 8.2)
- MySQL 8.0 (database server)

### 3. Run database migrations

To create the database schema, run the migrations inside the FrankenPHP container:

```bash
docker compose exec frankenphp php bin/console doctrine:migrations:migrate --no-interaction
```

### 4. Access the application

The application should now be accessible at:
- http://localhost (HTTP)
- https://localhost (HTTPS)

## Additional Commands

### Checking Symfony container

To debug the Symfony container:

```bash
docker compose exec frankenphp php bin/console debug:container
```

## Stopping the environment

To stop the Docker containers:

```bash
docker compose down
```

To stop and remove volumes (this will delete the database data):

```bash
docker compose down -v
```
