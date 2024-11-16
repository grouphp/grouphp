{ pkgs ? import <nixpkgs> {} }:

let
  builderPkgs = import ./builder.nix { inherit pkgs; };
in

pkgs.mkShell {
  # buildInputs is for dependencies you'd need "at run time",
  # were you to to use nix-build not nix-shell and build whatever you were working on
  #buildInputs = [
  #  builderPkgs.php
  #  builderPkgs.symfonyCli
  #  builderPkgs.just
  #];

  buildInputs = pkgs.lib.attrValues builderPkgs;

  shellHook = ''
      # Add any environment setup commands here if needed
      echo "PHP version: $(php -v)"
      echo "Symfony CLI version: $(symfony -v)"
      echo "Just version: $(just --version)"
  '';
}