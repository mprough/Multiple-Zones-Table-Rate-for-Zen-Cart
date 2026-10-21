# Version history

## 2.1.1 - 2026-10-21

- Advance the patch version for a new Zen Cart plugin repository upload.
- Clarify traditional installation through Modules > Shipping rather than Plugin Manager.
- Qualify PHP compatibility by the limits of the installed Zen Cart version.
- Document upgrading from 2.1.0 without removing existing shipping settings.
- Shipping calculations, configured zones, rates, and handling fees are unchanged.

## 2.1.0 - 2026-09-02

- Corrected the Zen Cart array-based language file so its definitions load properly.
- Made all 10 zone tables available regardless of how many zone definitions exist during installation.
- Stopped at the first matching zone so overlapping definitions do not silently select a later zone.
- Added percentage-rate support in price mode to match current Zen Cart table-rate behavior.
- Added protection against incomplete or invalid rate-table pairs.
- Added missing class properties to avoid PHP 8.2 and later dynamic-property notices.
- Added observer notification support and a module help link.
- Hardened destination values and database queries with integer casting.
- Updated documentation, installation guidance, licensing, and compatibility information.

## 2.0.1 - 2024-08-24

- Updated for Zen Cart 2.1.0 and PHP 8.3.

## Earlier releases

- Original multiple-zone table-rate implementation based on Zen Cart's built-in Table Rate shipping module.
