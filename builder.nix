{ pkgs ? import <nixpkgs> {} }:
let
  myPHP = pkgs.php83.buildEnv {
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
      xdebug.mode=off
      xdebug.start_with_request=yes
      memory_limit=256M
    '';
  };
in
{
  php = myPHP;
  symfony-cli = pkgs.symfony-cli;
  just = pkgs.just;
  process-compose = pkgs.process-compose;
  mailpit = pkgs.mailpit;
}