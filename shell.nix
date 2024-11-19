{ pkgs ? import <nixpkgs> {} }:

pkgs.mkShell {
  name = "grouphp-shell";
  buildInputs = [
    (import ./build/php.nix { inherit pkgs; })
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
