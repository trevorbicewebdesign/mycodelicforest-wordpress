<?php
/*
 * Integration suite bootstrap file.
 *
 * Loaded AFTER the suite modules are initialized and WordPress, the plugins and the theme named
 * in tests/integration.suite.yml are loaded.
 */
putenv('APPLICATION_ENV=test');
