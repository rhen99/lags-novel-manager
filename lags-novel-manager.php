<?php
/**
 * Plugin Name: LAGS Novel Manager
 * Description: Manage novels and chapters with a structured system.
 * Version: 1.0.0
 * Author: LAGS
 */

if (!defined('ABSPATH')) {
    exit;
}

define('LNM_PATH', plugin_dir_path(__FILE__));
define('LNM_URL', plugin_dir_url(__FILE__));

require_once LNM_PATH . 'includes/class-lnm-init.php';


add_action('plugins_loaded', function () {
    new LNM_Init();
});