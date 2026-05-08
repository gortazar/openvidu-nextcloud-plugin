# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] – 2024-01-01

### Added

- Initial release of the OpenVidu Integration Nextcloud app.
- Room management: create, list, and delete named meeting rooms.
- Room embed: join a meeting room via an embedded OpenVidu Meet iframe.
- Admin settings: configure the OpenVidu Meet server URL from the Nextcloud
  admin panel.
- REST API (`/api/rooms`) for programmatic room management.
- Database migration to create the `openviduintegration_rooms` table.
- Full unit-test suite (PHPUnit 11).
