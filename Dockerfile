ARG BUILDER

FROM $BUILDER AS builder

COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader

COPY . .
RUN <<EOF
  composer dump-autoload --optimize
  composer run-script post-install-cmd
EOF

#=======================================================================================================================
# Final image
#=======================================================================================================================
# We want the final image to be as small as possible, so we start from the base image and copy only the necessary files.
# TODO: Use only minimal dependencies for the final image.
#       Also we can trim down the final build. E.g. tests ere not needed.
#-----------------------------------------------------------------------------------------------------------------------
FROM $BUILDER
COPY --from=builder /app /app