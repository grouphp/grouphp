{
    # Pinning packages with URLs inside a Nix expression
    # https://nix.dev/tutorials/first-steps/towards-reproducibility-pinning-nixpkgs#pinning-packages-with-urls-inside-a-nix-expression
    # Picking the commit can be done via https://status.nixos.org,
    # which lists all the releases and the latest commit that has passed all tests.
    pkgs ? import (fetchTarball "https://github.com/NixOS/nixpkgs/archive/345c263f2f53a3710abe117f28a5cb86d0ba4059.tar.gz") {},

    php ? pkgs.php83.buildEnv {
          extensions = ({ enabled, all }: enabled ++ (with all; [
              redis
              openssl
              pcntl
              pdo_pgsql
              mbstring
              intl
              curl
              bcmath
              apcu
              xdebug
              xsl
          ]));
          extraConfig = ''
            xdebug.start_with_request=yes
            memory_limit=256M
          '';
        },
}:

pkgs.mkShell {
    buildInputs = [
        php
        pkgs.symfony-cli
        pkgs.just
        pkgs.process-compose
        pkgs.mailpit
        pkgs.postgresql
    ];

    shellHook = ''
        # Add the `bin` directory in the current directory to PATH
        export PATH="$PWD/bin:$PATH"
        # process-compose settings
        export PC_CONFIG_FILES=process-compose.yaml
        export PC_PORT_NUM=8081
        php -v
        composer -V
    '';
}
