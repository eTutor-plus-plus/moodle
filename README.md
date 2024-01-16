# Moodle Plugin

This project contains the Moodle-plugin for synchronizing the eTutor tasks.

## Development

To set up the development-environment execute the following steps:

1. Execute file `download-moodle.[sh|bat]`
2. Start docker containers `docker-compose up -d` and wait for containers to start up.
3. Execute file `setup-moodle.[sh|bat]`

After installation moodle is available at `http://localhost:8000/`.

Moodle-Admin-User Credentials: `admin`/`secret`

The Moodle containers can be started with `docker-compose up -d`

## Task Synchronization

See [this document](task-synchronization.md) for configuring Moodle to make the eTutor Task Synchronization work.

## Task Submission

Following settings must be made to the Moodle installation for the eTutor Task Submission to work:
