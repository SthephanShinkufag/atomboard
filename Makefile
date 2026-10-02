.PHONY: up down logs seed status reset test test-mysqli test-pgsql test-js

COMPOSE = docker compose --project-directory . -f dev/compose.yaml
TEST_COMPOSE = docker compose --project-directory . -f dev/compose.test.yaml
PG_TEST_COMPOSE = docker compose --project-directory . -f dev/compose.pg.test.yaml

up:
	$(COMPOSE) up -d --build --wait
	@echo "Board: http://localhost:$$( $(COMPOSE) port web 80 | sed 's/.*://' )/test/"

down:
	$(COMPOSE) down

logs:
	$(COMPOSE) logs -f

seed:
	$(COMPOSE) exec -T -u www-data web php dev/seed.php

status:
	$(COMPOSE) ps

reset:
	$(COMPOSE) down -v

test:
	@mkdir -p dev/coverage
	@trap '$(TEST_COMPOSE) down -v' EXIT; $(TEST_COMPOSE) run --build --rm tests

test-mysqli:
	@mkdir -p dev/coverage
	@trap '$(TEST_COMPOSE) down -v' EXIT; TEST_DB_MODE=mysqli $(TEST_COMPOSE) run --build --rm tests

test-pgsql:
	@mkdir -p dev/coverage/pgsql
	@trap '$(PG_TEST_COMPOSE) down -v' EXIT; $(PG_TEST_COMPOSE) run --build --rm tests

test-js:
	node --test dev/atomboard.test.cjs
