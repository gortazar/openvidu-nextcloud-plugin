# openvidu-nextcloud-plugin

A Nextcloud app that embeds [OpenVidu Meet](https://openvidu.io/Meet/) video-conferencing rooms directly inside your Nextcloud instance.

## Features

* **Room management** – create, list, and delete named meeting rooms from the Nextcloud UI.
* **Embedded meetings** – join a room via a full-page iframe; no separate browser tab required.
* **Admin settings** – administrators configure the OpenVidu Meet server URL once from the Nextcloud admin panel.
* **REST API** – a simple JSON API (`/api/rooms`) for programmatic integration.
* **Secure by design** – room tokens are randomly generated using Nextcloud's CSPRNG; only the room creator can delete their room.

## Requirements

| Dependency | Minimum version |
|------------|----------------|
| Nextcloud  | 28              |
| PHP        | 8.1             |

## Installation

### From source

```bash
# Clone into your Nextcloud apps directory
cd /var/www/nextcloud/apps
git clone https://github.com/gortazar/openvidu-nextcloud-plugin.git openviduintegration

# Install PHP dependencies
cd openviduintegration
make install

# Enable the app
php /var/www/nextcloud/occ app:enable openviduintegration
```

## Configuration

1. Go to **Admin → OpenVidu Integration** in the Nextcloud admin panel.
2. Enter the base URL of your OpenVidu Meet instance (e.g. `https://meet.example.com`).
3. Click **Save**.

Users can now open the **OpenVidu** entry in the Nextcloud navigation bar, create rooms, and join meetings.

## Development

```bash
# Install dev dependencies
make install

# Run unit tests
make test

# Run tests with HTML coverage report
make coverage
```

## Architecture

```
lib/
├── AppInfo/Application.php      # Nextcloud app bootstrap
├── Controller/
│   ├── PageController.php       # Page routes (index, room embed)
│   ├── ApiController.php        # REST API (CRUD rooms)
│   └── AdminController.php      # Admin settings API
├── Db/
│   ├── Room.php                 # ORM entity
│   └── RoomMapper.php           # DB queries
├── Migration/
│   └── Version0001Date…php      # DB schema migration
├── Service/
│   ├── RoomService.php          # Business logic
│   └── RoomNotFoundException.php
└── Settings/
    ├── Admin.php                # Admin form renderer
    └── AdminSection.php         # Admin section registration
```

## License

This project is licensed under the [GNU AGPL v3.0 or later](LICENSE).
