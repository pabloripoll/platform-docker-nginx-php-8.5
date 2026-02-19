#!/bin/bash
set -e

args=("$@")
USER=${args[0]}
GROUP=${args[1]}

# Validate required environment variables
if [ -z "${USER}" ] || [ -z "${GROUP}" ]; then
    echo "ERROR: USER and GROUP environment variables must be set"
    exit 1
fi

# Update Supervisord configuration
if [ ! -e supervisor/supervisord.conf ]; then
    # Update chown in [unix_http_server] section
    sed -i "s/^chown=.*/chown=${USER}:${GROUP}/" ./docker/config/supervisor/supervisord.conf
else
    echo "WARNING: ./docker/config/supervisor/supervisord.conf not found"
fi
