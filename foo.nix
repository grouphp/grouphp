with import(./builder.nix) { inherit pkgs php symfony-cli just process-compose mailpit; };
pkgs.mkShell {
  nativeBuildInputs = [
    php
    symfony-cli
    just
    process-compose
    mailpit
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
