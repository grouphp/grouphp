{ pkgs ? import <nixpkgs> {} }:

let
  myPHP = pkgs.php83.buildEnv {
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
  };
in

# https://ryantm.github.io/nixpkgs/builders/images/dockertools/
pkgs.dockerTools.buildImage {
  name = "grouphp";
  created = "now";
  tag = "latest";
  copyToRoot = pkgs.buildEnv {
    name = "files";
    paths = [ pkgs.bashInteractive myPHP ];
    pathsToLink = ["/bin"];
  };

  config = {
    Entrypoint = ["/bin/bash"];
    WorkingDir = "/data";
  };
}