# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project overview

A Symfony bundle (`ekrouzek/pagination-filters-bundle`) that adds pagination, filtering, and sorting to API endpoints built on FOSRestBundle + Doctrine ORM. It's a library, not an application, but it does have a PHPUnit test suite (see Testing below); there is no separate build step or linter config.

Dependencies (see `composer.json`): PHP >=8.0.1, `friendsofsymfony/rest-bundle` (for `ParamFetcher`/`View`), `doctrine/orm`, `nette/utils` (for the `Paginator`), `symfony/uid` (for the `Uuid` data field).

## Testing

PHP/Composer don't need to be installed locally — a Docker Compose setup (`.docker/php/Dockerfile`) provides PHP 8.1 + pdo_sqlite. From the project root:

```bash
docker compose run --rm php composer install   # install/update dependencies
docker compose run --rm php composer test       # run the PHPUnit suite
```

Tests build a real Doctrine `QueryBuilder` against an in-memory SQLite database (`tests/Fixtures/EntityManagerFactory.php`) rather than mocking Doctrine, so filter/sort DQL output is checked against actual Doctrine behavior. The same `composer test` command runs in CI (`.gitlab-ci.yml`) before a tagged release is published. When adding a require to `composer.json`, use `docker compose run --rm php composer require <package>` (not a manual edit) so `composer.lock` stays in sync — an edited-but-unlocked require fails `composer install` in CI.

Publishing is via GitLab CI (`.gitlab-ci.yml`): pushing a git tag triggers a job that publishes the tagged version to a GitLab Composer package registry. There's no separate build step.

## Architecture

Entry point: `Pagination/PaginationHandler.php`. A controller constructs one per request with the FOSRestBundle `ParamFetcher`, optionally calls `createQueryFilter()` to configure filterable/sortable fields, then calls `getPaginatedData($queryBuilder)` to get filtered+sorted+paginated results, and `sendPaginatedResponse($items)` to wrap them with a `_pagination` header. See `README.md` for the full usage walkthrough (it's the canonical usage doc — keep it in sync with behavior changes).

Filtering/sorting configuration and logic lives in `QueryFilter/QueryFilter.php`. Fields are registered with a public **key** (the name usable from the `filter`/`sort` query params) mapped to a DQL **dbKey** (e.g. `addTextField("name", "c.name")`), each backed by a `DataField` subtype (`Text`/`Number`/`Datetime`/`Boolean`/`Uuid` in `QueryFilter/DataField/`) that knows which comparison methods it supports and how to render each one (`eq`, `neq`, `like`, `not-like`, `lt`, `lte`, `gt`, `gte`, plus `is-null`/`is-not-null`/`is-member-of`) into a Doctrine `Expr`. Unsupported ops per field type throw `UnsupportedDataFieldMethodException` (e.g. text fields reject `lt`/`lte`/`gt`/`gte`).

The `filter` query string is parsed in three stages inside `QueryFilter`:
1. **Tokenize** (`tokenizeFilter`) — splits the raw string into `FilterToken` objects (`TokenExpression`, `TokenAnd`, `TokenOr`, `TokenBracketLeft`/`Right`) from `QueryFilter/FilterToken/`.
2. **Build a binary expression tree** (`createBinaryExpressionTree`) — a combined infix-to-postfix + tree-construction pass over the tokens, using `AND`/`OR` operator priority and bracket handling. Nodes are `BinaryEvaluationTreeNode` (operators live on internal nodes, operands/expressions on leaves).
3. **Evaluate the tree** (`getQueryExpressionFromTree`) — recursively calls `combineExpr()` on each node's token. Leaf `TokenExpression` nodes parse their `method:field:value` string, look up the matching `DataField` by key, and dispatch to its comparison method to produce a Doctrine `Andx|Orx|Comparison`. Internal `TokenAnd`/`TokenOr` nodes combine their children's expressions via `$queryBuilder->expr()->andX()/orX()`.

Sorting (`QueryFilter::sort`) parses a single `field:asc|desc` pair (or falls back to `addDefaultSort()` entries) and resolves the field's dbKey the same way as filtering, via the registered `DataField`s.

`PaginationHandler::getPaginatedData` applies filter, then sort, then uses `nette/utils` `Paginator` for the page-math and Doctrine's `Tools\Pagination\Paginator` to actually execute/hydrate the paginated query (array hydration mode).

Extending with a new comparison operator or field type means: add the method to `DataField` (default impl) and override/reject it in the relevant subtypes, then wire it into `TokenExpression::combineExpr`'s method switch.

Exceptions live in `QueryFilter/Exception/`; both `FilterParseException` and `SortParseException` extend `PaginationAndFilterException`, which is the one type `PaginationHandler`/controllers are expected to catch.
