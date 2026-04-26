# Debugging PHP with Xdebug

This project's Sail setup includes Xdebug support out of the box.

## Setup

### 1. Enable Xdebug in `.env`

```
SAIL_XDEBUG_MODE=debug
SAIL_XDEBUG_CONFIG="client_host=host.docker.internal"
```

### 2. Rebuild and restart Sail

```bash
vendor/bin/sail down && vendor/bin/sail up -d
```

### 3. VSCode: install the PHP Debug extension

Install [PHP Debug](https://marketplace.visualstudio.com/items?itemName=xdebug.php-debug) (`xdebug.php-debug`).

The `.vscode/launch.json` is already committed with the correct path mappings:

```json
{
    "version": "0.2.0",
    "configurations": [
        {
            "name": "Listen for Xdebug",
            "type": "php",
            "request": "launch",
            "port": 9003,
            "pathMappings": {
                "/var/www/html": "${workspaceFolder}"
            }
        }
    ]
}
```

## How it works

Xdebug runs in **client mode**: the PHP container dials out to `host.docker.internal:9003` (your machine). VSCode listens on that port — no port needs to be published in `compose.yaml`.

## Usage

1. Start the debugger in VSCode: **Run > Start Debugging** (F5)
2. Set a breakpoint
3. Hit a route in the browser

### Debugging CLI / Artisan commands

Set `XDEBUG_SESSION=1` before the command:

```bash
XDEBUG_SESSION=1 vendor/bin/sail artisan your:command
```

## Verify Xdebug is active

```bash
vendor/bin/sail php -m | grep xdebug
vendor/bin/sail php -i | grep xdebug.mode
```

The second command should output `xdebug.mode => debug`.

## Performance note

Xdebug adds ~30% overhead on every request even when no debugger is connected. Keep `SAIL_XDEBUG_MODE=off` in `.env` when not actively debugging.
