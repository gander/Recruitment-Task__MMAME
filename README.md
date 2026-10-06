# Recruitment Task: MMAME

[![CI](https://github.com/gander/Recruitment-Task__MMAME/actions/workflows/ci.yml/badge.svg)](https://github.com/gander/Recruitment-Task__MMAME/actions/workflows/ci.yml)

Zadanie: aplikacja w Symfony wyświetlająca listę umów (`contracts`) z bazy MySQL. Strona główna filtruje i sortuje rekordy na podstawie parametrów `akcja`, `sort` i `i` w adresie URL, a wynik renderuje w szablonie Twig. Schemat bazy i dane przykładowe tworzą migracje Doctrine.

## Requirements

- Docker Engine z Docker Compose v2 (jedyna zależność; PHP ani Composer na hoście nie są potrzebne).
- `curl` do przykładów użycia.

## Install

```bash
docker compose run --rm frankenphp composer install
docker compose up --build -d --wait
docker compose exec frankenphp php bin/console doctrine:migrations:migrate --no-interaction
```

## Usage

Aplikacja: <http://localhost:8080> (np. <http://localhost:8080/?akcja=5&sort=1&i=1>).

## Test

Po kroku Install:

```bash
docker compose exec frankenphp php bin/console lint:container
```

Projekt nie zawiera testów PHPUnit; CI uruchamia `composer validate`, `composer audit` (oba nieblokujące, bo lockfile jest nieaktualny) i `docker compose config`.

## Override

Lokalne zmiany (np. inny port) trzymaj w `compose.override.yml`, który jest ignorowany przez git:

```bash
cat > compose.override.yml <<'OVERRIDE'
services:
  frankenphp:
    ports: !override
      - '8081:8080'
OVERRIDE
docker compose up -d
```

## Cleanup

```bash
docker compose down -v --rmi local --remove-orphans
rm -f compose.override.yml
```
