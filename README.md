# Moodle Plugin

This project contains the Moodle-plugin for synchronizing the eTutor tasks.

## Development

To setup the development-environment execute the following steps:

1. Start Docker Engine
2. Execute file `download-moodle.[sh|bat]`
3. Start docker containers `docker-compose up -d` and wait for containers to start up.
4. Execute file `setup-moodle.[sh|bat]`
5. See the `readme_moodle.txt` on how to enable the plugin.

After installation moodle is available at `http://localhost:8000/`.

Moodle-Admin-User Credentials: `admin`/`secret`

The Moodle containers can be started with `docker-compose up -d`
