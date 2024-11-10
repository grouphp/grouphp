format:
    vendor/bin/ecs --fix

test:
    vendor/bin/psalm
    vendor/bin/phpstan

start:
    bin/rr serve -c .rr.dev.yaml --debug