<?php

if (!defined('ABSPATH')) {
    exit;
}

class LNM_Init{
      public function __construct() {
        $this->load_files();
        $this->init_classes();
    }
     private function load_files() {
        require_once LNM_PATH . 'includes/class-lnm-cpt.php';
        require_once LNM_PATH . 'includes/class-lnm-meta.php';
        require_once LNM_PATH . 'includes/class-lnm-admin.php';
        require_once LNM_PATH . 'includes/class-lnm-frontend.php';
    }

    private function init_classes() {
        new LNM_CPT();
        new LNM_Meta();
        new LNM_Admin();
        new LNM_Frontend();
    }
}