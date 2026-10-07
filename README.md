# Recruitment Task: MMAME

[![CI](https://github.com/gander/Recruitment-Task__MMAME/actions/workflows/ci.yml/badge.svg)](https://github.com/gander/Recruitment-Task__MMAME/actions/workflows/ci.yml)

Task: a Symfony application that lists contracts (`contracts`) from a SQLite database. The home page filters and sorts the records based on the `akcja`, `sort` and `i` URL parameters and renders the result in a Twig template. Doctrine migrations create the database schema and the sample data.

## Requirements

- Docker Engine with Docker Compose v2 (the only dependency; neither PHP nor Composer is needed on the host).
- `curl` for the usage examples.

## Install

```bash
docker compose up --build -d --wait
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
```

## Usage

Application: <http://localhost:8080> (e.g. <http://localhost:8080/?akcja=5&sort=1&i=1>).

## Test

After the Install step:

```bash
docker compose exec app php bin/console lint:container
```

Unit tests (PHPUnit) cover the `IndexAction` controller (query building from the URL parameters and rendering):

```bash
docker compose run --rm --no-deps app vendor/bin/phpunit
```

CI additionally runs `composer validate`, `composer audit` (both non-blocking because the lock file is outdated), `docker compose config`, Rector and ECS.

## Override

Keep local changes (e.g. a different port) in `compose.override.yml`, which is ignored by git:

```bash
cat > compose.override.yml <<'OVERRIDE'
services:
  app:
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

## License

This project is licensed under the [PolyForm Noncommercial License 1.0.0](https://polyformproject.org/licenses/noncommercial/1.0.0)
with additional terms (see [LICENSE](LICENSE)). In short: you may read the code and run it to evaluate the
author's job application, but you may not use it commercially, in your company's operations, or as assessment
material in any other hiring process.
