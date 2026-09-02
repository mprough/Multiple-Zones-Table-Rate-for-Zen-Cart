# Installation and upgrade

## Before installing

Create the geographic zones you need under **Locations/Taxes > Zone Definitions** in the Zen Cart admin. Add the countries and regions for each definition before configuring this shipping module.

## New installation

1. Back up the store files and database.
2. Copy the repository's `includes` directory into the root of the Zen Cart store. The upload adds two files and does not overwrite a Zen Cart core file.
3. In the admin, go to **Modules > Shipping**.
4. Select **Multiple Zones Table Rate** (`zonetable`) and click **Install Module**.
5. Configure the calculation method, tax settings, shipping zones, rate tables, handling fees, and sort order.
6. Test addresses in every configured zone and at every rate boundary before enabling the module for shoppers.

## Upgrade from an earlier version

1. Back up the store files and database.
2. Record the current `zonetable` settings under **Modules > Shipping**.
3. Upload the new `includes` directory over the earlier module files.
4. If fewer than 10 zone sections are shown, remove and reinstall only the `zonetable` shipping module, then re-enter the recorded settings. Removing the module deletes its configuration rows but does not affect orders or zone definitions.
5. Test all configured zones and rate boundaries.

## Uninstall

In **Modules > Shipping**, select **Multiple Zones Table Rate** and click **Remove Module**. Then delete:

- `includes/modules/shipping/zonetable.php`
- `includes/languages/english/modules/shipping/lang.zonetable.php`

Removing the module deletes only the module's registered configuration keys. It does not remove orders or zone definitions.
