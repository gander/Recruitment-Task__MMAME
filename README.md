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

The migrations create the `contracts` table and its 30 sample rows.

## Usage

Application: <http://localhost:8080>.

- Without parameters the page lists all 30 contracts (`id` and name) ordered by `id`.
- With `akcja=5` only contracts with an amount above 10 and `id` equal to `i` are listed, sorted by `sort` (`1`: name and NIP, `2`: amount descending); names of contracts above 5 get the amount appended with two decimals.

```bash
curl 'http://localhost:8080/?akcja=5&sort=1&i=4'
```

Expected response, status 200, a table with one row:

```html
<td>4</td>
<td>GHI Trading 15.99</td>
```

## Test

Run the whole test suite (PHPUnit) in Docker:

```bash
docker compose run --rm --build --no-deps app composer test
```

Run the quality checks (Rector dry run and ECS), which CI runs too:

```bash
docker compose run --rm --build --no-deps app composer check
```

Check the container (needs the running application, see Install):

```bash
docker compose exec app php bin/console lint:container
```

CI (`.github/workflows/ci.yml`) runs the jobs `checks` (`composer validate`, `composer audit`, `docker compose config`), `quality`, `tests` (PHP 8.4, plus a non-blocking PHP 8.5 run), `outdated` and `smoke` (builds the image and replays the requests from Usage). Optional pre-commit hooks that run Rector, ECS and `swiss-knife breakpoint` in Docker: `lefthook install`.

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
