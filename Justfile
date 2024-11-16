format:
    vendor/bin/ecs --fix

test:
    vendor/bin/psalm
    vendor/bin/phpstan

up:
    process-compose up

down:
    process-compose down

setup:
    initdb --pgdata=.data/postgres --username=root --pwfile=.POSTGRES_DATABASE_PASSWORD
    process-compose up -D # TODO: properly implement readiness probes
    composer install
    bin/console event-sourcing:database:create
    bin/console event-sourcing:schema:create
    bin/console doctrine:database:create
    bin/console messenger:setup-transports
    bin/console doctrine:fixtures:load --no-interaction
    process-compose down

setup-from-scratch:
    # the - ignores the status code.
    # When we cannot stop a project it is not started, so this one is fine.
    -process-compose down
    rm -rf .data/
    just setup