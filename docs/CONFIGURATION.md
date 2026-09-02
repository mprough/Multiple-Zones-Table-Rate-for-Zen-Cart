# Configuration guide

## Calculation method

Choose one method for all configured zones:

- `weight`: total shippable weight
- `price`: shippable order value
- `item`: total number of shippable items

Products handled by Zen Cart's free-shipping rules are excluded from the applicable total.

## Zone tables

Each of the 10 sections contains a shipping-zone selector, rate table, and handling fee. Unused sections should remain set to `--none--`.

Rate tables use comma-separated `maximum:cost` pairs in ascending order. For example:

```text
3:8.50,7:10.50,99:20.00
```

This charges 8.50 up to 3 units, 10.50 above 3 through 7 units, and 20.00 above 7 through 99 units. The unit is weight, price, or item count according to the selected calculation method.

When using price mode, a rate may be a percentage:

```text
50:8.50,100:7%,10000:5%
```

Always include a final maximum high enough to cover the largest expected order. If an order exceeds the last maximum, the module has no matching bracket and its calculated table cost is zero, apart from any handling fee.

## Overlapping zones

The first configured zone that matches the delivery address is used. Put the most specific zone first and broader zones later. Every country or region intended to receive this method must belong to a selected Zen Cart zone definition.

## Testing checklist

- Test an address inside every configured zone.
- Test an address outside all configured zones; the method should not appear.
- Test values immediately below, at, and above every bracket maximum.
- Test carts containing free-shipping products.
- Test tax calculation and the displayed zone name.
