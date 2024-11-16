format:
    vendor/bin/ecs --fix

test:
    vendor/bin/psalm
    vendor/bin/phpstan

start:
    process-compose up -D

attach:
    process-compose attach

stop:
    process-compose down

initialize:
    initdb --pgdata=.data/postgres --username=root --pwfile=.POSTGRES_DATABASE_PASSWORD
    composer install
    process-compose up -D
    bin/console event-sourcing:database:create
    bin/console event-sourcing:schema:create
    bin/console doctrine:database:create
    bin/console messenger:setup-transports

reinitialize:
    # the - ignores the status code.
    # When we cannot stop a project it is not started, so this one is fine.
    -just stop
    rm -rf .data/
    just initialize