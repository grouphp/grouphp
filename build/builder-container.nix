{ pkgs ? import <nixpkgs> {} }:

# https://ryantm.github.io/nixpkgs/builders/images/dockertools/
pkgs.dockerTools.buildImage {
  name = "grouphp";
  created = "now";
  tag = "latest";
  copyToRoot = pkgs.buildEnv {
    name = "files";
    paths = [
        # Installs php and stuff
        (import ./php.nix { inherit pkgs; })

        # Add a minimal shell to the container
        pkgs.bashInteractive
        pkgs.symfony-cli
        pkgs.just
        pkgs.process-compose
        pkgs.mailpit
        pkgs.postgresql
    ];
  };

  config = {
    # Set the entrypoint or ensure the shell exists
    Cmd = [ "/bin/bash" ];
    WorkingDir = "/app";
  };
}