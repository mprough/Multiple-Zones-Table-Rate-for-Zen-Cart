<?php
/**
 * @package shippingMethod
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: zonetable.php v2.02 08/15/2024 PRO-Webs.net $
 */

class zonetable extends base {
    public string $code;
    public string $title;
    public string $description;
    public string $icon;
    public bool $enabled;
    public int $num_zones;
    public int $dest_zone;

    public function __construct() {
        global $order, $db;

        $geozones = $db->Execute("SELECT * FROM " . TABLE_GEO_ZONES);
        $this->num_zones = $geozones->RecordCount();

        $this->code = 'zonetable';
        $this->title = MODULE_SHIPPING_ZONETABLE_TEXT_TITLE;
        $this->description = MODULE_SHIPPING_ZONETABLE_TEXT_DESCRIPTION;
        $this->sort_order = defined('MODULE_SHIPPING_ZONETABLE_SORT_ORDER') ? MODULE_SHIPPING_ZONETABLE_SORT_ORDER : null;
        if (null === $this->sort_order) return false;
        $this->icon = '';
        $this->tax_class = MODULE_SHIPPING_ZONETABLE_TAX_CLASS;
        $this->tax_basis = MODULE_SHIPPING_ZONETABLE_TAX_BASIS;
        $this->enabled = zen_get_shipping_enabled($this->code) && MODULE_SHIPPING_ZONETABLE_STATUS === 'True';

        if ($this->enabled) {
            $this->dest_zone = 0;
            for ($i = 1; $i <= $this->num_zones; $i++) {
                if ((int)constant('MODULE_SHIPPING_ZONETABLE_ZONE_' . $i) > 0) {
                    $check = $db->Execute("SELECT zone_id FROM " . TABLE_ZONES_TO_GEO_ZONES . " WHERE geo_zone_id = '" . constant('MODULE_SHIPPING_ZONETABLE_ZONE_' . $i) . "' AND zone_country_id = '" . $order->delivery['country']['id'] . "' ORDER BY zone_id");
                    while (!$check->EOF) {
                        if ($check->fields['zone_id'] < 1 || $check->fields['zone_id'] == $order->delivery['zone_id']) {
                            $this->dest_zone = $i;
                            break;
                        }
                        $check->MoveNext();
                    }
                }
            }
            if ($this->dest_zone < 1) {
                $this->enabled = false;
            }
        }
    }

    public function quote(string $method = ''): array {
        global $order, $shipping_weight, $shipping_num_boxes, $total_count, $db;

        switch (MODULE_SHIPPING_ZONETABLE_MODE) {
            case 'price':
                $order_total = $_SESSION['cart']->show_total() - $_SESSION['cart']->free_shipping_prices();
                break;
            case 'weight':
                $order_total = $shipping_weight;
                break;
            case 'item':
                $order_total = $total_count - $_SESSION['cart']->free_shipping_items();
                break;
            default:
                $order_total = 0;
        }

        $table_cost = preg_split("/[:,]/", constant('MODULE_SHIPPING_ZONETABLE_COST_' . $this->dest_zone));
        $shipping = 0;

        for ($i = 0, $n = count($table_cost); $i < $n; $i += 2) {
            if (round($order_total, 9) <= $table_cost[$i]) {
                $shipping = $table_cost[$i + 1];
                break;
            }
        }

        if (MODULE_SHIPPING_ZONETABLE_MODE === 'weight') {
            $shipping *= $shipping_num_boxes;

            $show_box_weight = match (SHIPPING_BOX_WEIGHT_DISPLAY) {
                0 => '',
                1 => ' (' . $shipping_num_boxes . ' ' . TEXT_SHIPPING_BOXES . ')',
                2 => ' (' . number_format($shipping_weight * $shipping_num_boxes, 2) . TEXT_SHIPPING_WEIGHT . ')',
                default => ' (' . $shipping_num_boxes . ' x ' . number_format($shipping_weight, 2) . TEXT_SHIPPING_WEIGHT . ')',
            };
        } else {
            $show_box_weight = '';
        }

        $get_gzn = $db->Execute("SELECT geo_zone_name FROM " . TABLE_GEO_ZONES . " WHERE geo_zone_id = '" . constant('MODULE_SHIPPING_ZONETABLE_ZONE_' . $this->dest_zone) . "' LIMIT 1");
        $gzn = $get_gzn->fields['geo_zone_name'] ?? '';

        $this->quotes = [
            'id' => $this->code,
            'module' => MODULE_SHIPPING_ZONETABLE_TEXT_TITLE . $show_box_weight,
            'methods' => [
                [
                    'id' => $this->code,
                    'title' => MODULE_SHIPPING_ZONETABLE_TEXT_WAY . $gzn,
                    'cost' => $shipping + constant('MODULE_SHIPPING_ZONETABLE_HANDLING_' . $this->dest_zone)
                ]
            ]
        ];

        if ($this->tax_class > 0) {
            $this->quotes['tax'] = zen_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
        }

        if (zen_not_null($this->icon)) {
            $this->quotes['icon'] = zen_image($this->icon, $this->title);
        }

        return $this->quotes;
    }

    public function check(): int {
        global $db;
        if (!isset($this->_check)) {
            $check_query = $db->Execute("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_SHIPPING_ZONETABLE_STATUS'");
            $this->_check = $check_query->RecordCount();
        }
        return $this->_check;
    }

    public function install(): void {
        global $db;
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Table Method', 'MODULE_SHIPPING_ZONETABLE_STATUS', 'True', 'Do you want to offer zonetable rate shipping?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', NOW())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Table Method', 'MODULE_SHIPPING_ZONETABLE_MODE', 'weight', 'The shipping cost is based on the order total or the total weight of the items ordered or the total number of items ordered.', '6', '0', 'zen_cfg_select_option(array(\'weight\', \'price\', \'item\'), ', NOW())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Tax Class', 'MODULE_SHIPPING_ZONETABLE_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', NOW())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Tax Basis', 'MODULE_SHIPPING_ZONETABLE_TAX_BASIS', 'Shipping', 'On what basis is Shipping Tax calculated. Options are<br />Shipping - Based on customers Shipping Address<br />Billing - Based on customers Billing address<br />Store - Based on Store address if Billing/Shipping Zone equals Store zone', '6', '0', 'zen_cfg_select_option(array(\'Shipping\', \'Billing\', \'Store\'), ', NOW())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort Order', 'MODULE_SHIPPING_ZONETABLE_SORT_ORDER', '0', 'Sort order of display.', '6', '0', NOW())");

        for ($i = 1; $i <= $this->num_zones; $i++) {
            $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Shipping Zone " . $i . "', 'MODULE_SHIPPING_ZONETABLE_ZONE_" . $i . "', '0', 'If a zone is selected, only enable this shipping method for that zone.', '6', '0', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', NOW())");
            $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Table of Rates " . $i . "', 'MODULE_SHIPPING_ZONETABLE_COST_" . $i . "', '3:8.50,7:10.50,99:20.00', 'Shipping rates to Zone " . $i . " destinations based on a group of maximum order totals. Example: 3:8.50,7:10.50,... This means that orders up to 3 cost 8.50 for Zone " . $i . " destinations.', '6', '0', NOW())");
            $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Handling Fee for Zone " . $i . "', 'MODULE_SHIPPING_ZONETABLE_HANDLING_" . $i . "', '0', 'Handling Fee for this shipping zone', '6', '0', NOW())");
        }
    }

    public function remove(): void {
        global $db;
        $db->Execute("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    public function keys(): array {
        $keys = ['MODULE_SHIPPING_ZONETABLE_STATUS', 'MODULE_SHIPPING_ZONETABLE_MODE', 'MODULE_SHIPPING_ZONETABLE_TAX_CLASS', 'MODULE_SHIPPING_ZONETABLE_TAX_BASIS', 'MODULE_SHIPPING_ZONETABLE_SORT_ORDER'];
        for ($i = 1; $i <= $this->num_zones; $i++) {
            $keys[] = 'MODULE_SHIPPING_ZONETABLE_ZONE_' . $i;
            $keys[] = 'MODULE_SHIPPING_ZONETABLE_COST_' . $i;
            $keys[] = 'MODULE_SHIPPING_ZONETABLE_HANDLING_' . $i;
        }
        return $keys;
    }
}

