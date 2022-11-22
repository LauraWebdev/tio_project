# TIO Project

Simple Event/Ticket system written in Symfony with API and testing.

## Setup

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --pull --no-cache` to build fresh images
3. Run `docker compose up` (the logs will be displayed in the current shell)
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker compose down --remove-orphans` to stop the Docker containers.

## Credits
- Symfony Docker Bootstrap by [Kévin Dunglas](https://dunglas.fr)
- Uses [MaterialDesignIcons](https://materialdesignicons.com)
- Uses html5doctor.com Reset Stylesheet by [Richard Clark](http://richclarkdesign.com)
