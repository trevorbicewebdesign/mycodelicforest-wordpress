<?php

// Camp history: the `camp_year` post type and its fields are defined in ACF
// while the model is still settling. ACF reads/writes them as local JSON in this plugin's
// acf-json/ folder so every change made in the ACF admin UI is versioned with the plugin.
class MycodelicForestHistory {

    public function __construct() {

    }

    public function init() {
        add_filter('acf/settings/save_json', [$this, 'acfJsonPath']);
        add_filter('acf/settings/load_json', [$this, 'acfLoadJsonPaths']);
    }

    public function acfJsonPath() {
        return MYCO_CORE_ABS_PATH . 'acf-json';
    }

    public function acfLoadJsonPaths($paths) {
        $paths[] = $this->acfJsonPath();
        return $paths;
    }
}
