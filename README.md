# Query Pulse

A Laravel package that records database queries per HTTP request, stores lightweight history in your database, and surfaces **slow queries**, **duplicate bursts**, **probable N+1**, **wildcard `SELECT *`**, and aggregate time/count — via a **web dashboard**, **CLI**, and **heuristic scoring**.

## Requirements

- PHP `^8.1`
- Laravel `10.x`, `11.x`, or `12.x` (`illuminate/contracts`, `illuminate/support`)

The package uses Laravel’s query events and schema builder; any **database connection supported by Laravel** (MySQL, PostgreSQL, SQLite, SQL Server, etc.) can be used for the app and for the package tables.

## Installation

Install with Composer:

```bash
composer require alfinprdht/query-pulse
```

The service provider is **auto-discovered**. Run migrations so the `query_pulse` and `query_pulse_report` tables exist:

```bash
php artisan migrate
```

### Dashboard authentication (recommended)

The dashboard is protected by **HTTP Basic Auth**.

Set credentials in your app environment:

```bash
QUERY_PULSE_AUTH_USERNAME=admin
QUERY_PULSE_AUTH_PASSWORD=strong-password
```

Also, if we want to define a specific IP that is allowed to access the query pulse dashboard, set the following environment variables :

```bash
QUERY_PULSE_ALLOWED_VPN_IPS=127.0.0.1,127.0.0.2
```
By default, IP check middleware is disabled.

### Publish configuration (optional)

```bash
php artisan vendor:publish --tag=query-pulse-config
```

This copies `config/query-pulse.php` into your application so you can adjust thresholds and URLs without editing the vendor package.

### Publish views (optional)

```bash
php artisan vendor:publish --tag=query-pulse-views
```

Overrides live under `resources/views/vendor/query-pulse/`.

## How it works

- Middleware is registered globally: on each request (unless disabled or ignored), the package **listens** to Laravel’s DB layer and, after the response, **persists** metrics and a snapshot of executed queries for that URL (method + path).
- Dashboard routes are registered under the `/query-pulse` prefix and use the `web` middleware plus **HTTP Basic Auth** (set `QUERY_PULSE_AUTH_USERNAME` / `QUERY_PULSE_AUTH_PASSWORD`).
- Dashboard UI assets (Tailwind runtime + fonts) are served by the package under `/query-pulse/assets/*` (no CDN and no `vendor:publish` required).
- Analysis can run **automatically** (after enough new samples per URL) or you can trigger a **report** from Artisan.

## Configuration

Configuration file: `config/query-pulse.php` (publish or use merged defaults).

### Enable / disable

| Env | Default | Description |
|-----|---------|-------------|
| `QUERY_PULSE_ENABLED` | `true` | Set to `false` to turn off collection entirely (recommended for production unless you explicitly want recording). |

### Thresholds (milliseconds / counts)

| Env | Default | Meaning |
|-----|---------|---------|
| `QUERY_PULSE_SLOW_QUERY_TIME` | `100` | A single query is “slow” if its duration exceeds this (ms). |
| `QUERY_PULSE_DUPLICATE_BURST` | `10` | Same SQL+bindings repeated more than this count in one request suggests a duplicate burst. |
| `QUERY_PULSE_PROBABLE_N_PLUS_1` | `5` | Same SQL shape with many distinct bindings sets on the same code location may indicate probable N+1. |
| `QUERY_PULSE_TOTAL_QUERY_TIME` | `300` | Total query time in the analyzed batch (ms) used for scoring. |
| `QUERY_PULSE_TOTAL_QUERY_COUNT` | `75` | Total query count in the analyzed batch used for scoring. |

### Other options

- **`ignored_urls`** — Request paths excluded from collection (default includes `query-pulse`, `query-pulse/*`, and `.well-known/*`).
- **`auto_generate_report_every`** — Env: `QUERY_PULSE_AUTO_GENERATE_REPORT_EVERY` (default `100`). After this many new `query_pulse` rows for a URL since the last report update, analysis runs again. Set to `0` to disable auto re-analysis from this counter.
- **`enabled_url_stack_trace`** — When matched by `$request->is(...)`, enriches captured queries with an application stack frame (can impact performance and storage; tighten patterns in production).

## Usage

### Web dashboard

After authentication, open:

- **Dashboard:** `/query-pulse`
- **Report:** `/query-pulse/report/{reportId}` — `reportId` is the MD5 (32 hex characters) of the full URL key (e.g. `GET orders/123`).

### Artisan

Print a summary for a given endpoint key (HTTP method, space, path):

```bash
php artisan query-pulse:url 'GET dashboard/overview'
```

Example output:

```text
Generating report for URL: GET orders/402
+----------------------------+-------+
| Metrics                    | Value |
+----------------------------+-------+
| Slow Query                 | 1     |
| Duplicate Burst            | 1     |
| Probable N+1              | 1     |
| suspicious Wildcard Fetch  | 65    |
| Total Query Time           | 621.51 ms |
| Total Query Count          | 72    |
+----------------------------+-------+
Score: 47
Status: POOR
See the report at: https://your-app.test/query-pulse/report/...
```

## Scoring

The score starts at **100** and is reduced (never below **0**) as follows (see `ScoreCalculator`):

| Signal | Penalty |
|--------|---------|
| Slow queries | `−10` × *number of slow queries* in the analyzed batch |
| Suspicious wildcard `SELECT *` | `−(wildcard count / 5)` (can be fractional) |
| Duplicate burst | `−10` if *any* duplicate-burst issue is detected |
| Probable N+1 | `−10` if *any* probable N+1 issue is detected |
| Total query time | `−10` if total time exceeds `QUERY_PULSE_TOTAL_QUERY_TIME` |
| Total query count | `−10` if total count exceeds `QUERY_PULSE_TOTAL_QUERY_COUNT` |

**Status bands:**

| Status | Score |
|--------|-------|
| CRITICAL | ≤ 39 |
| POOR | 40 – 69 |
| WATCH | 70 – 89 |
| HEALTHY | ≥ 90 |

## Security & privacy

- Prefer **`QUERY_PULSE_ENABLED=false`** in production unless the risk is accepted and access is tightly controlled.
- Bindings are not saved in the database; they are hashed in order to analyze the process.
- Anyone with **shell access** can run `query-pulse:url`; treat server access accordingly.

## Notes about view publishing

If you publish views (`--tag=query-pulse-views`), your application will use the copies in `resources/views/vendor/query-pulse/`.
When upgrading the package, you may need to re-publish (or manually update) those views to pick up dashboard fixes (including asset URLs under `/query-pulse/assets/*`).

## Development

Clone the package and run tests:

```bash
composer install
composer test
```

## License

MIT
