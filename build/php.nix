{ pkgs ? import <nixpkgs> {} }:

pkgs.php83.buildEnv {
  extensions = ({ enabled, all }: enabled ++ (with all; [
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
    xdebug.mode=off
    xdebug.start_with_request=yes
    memory_limit=256M
  '';
}
