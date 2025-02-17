<?php
namespace Helper\BwbHelper;

use \Faker;
use \Exception;

/**
 * Class BwbHelper
 *
 * Helper class for managing sale configurations.
 */
class BwbHelper extends \Codeception\Module {
    /**
     * @var \Helper\WPDb Instance of WPDb module.
     */
    private $WPDb;

    /**
     * @var string Table prefix.
     */
    private $prefix;

    /**
     * Setup before suite.
     *
     * @param array $settings Suite settings.
     * @return void
     */
    public function _beforeSuite($settings = array()){
        // Initialization logic as before
        if ($this->hasModule('lucatume\WPBrowser\Module\WPDb')) {
            $this->WPDb = $this->getModule('lucatume\WPBrowser\Module\WPDb');
            $this->prefix = $this->WPDb->grabTablePrefix();
        } else if ($this->hasModule('\Helper\BwbHelper\WordpressDbHandler')) {
            $this->WPDb = $this->getModule('\Helper\BwbHelper\WordpressDbHandler');
            $this->prefix = '';
        }
    }

    /**
     * Execute a query.
     *
     * @param string $query SQL query.
     * @return mixed Query result.
     */
    public function query($query) {
        if ($this->WPDb instanceof \Helper\BwbHelper\WordpressDbHandler) {
            return $this->WPDb->query($query);
        }

        $dbh = $this->WPDb->dbh;
        $sth = $dbh->prepare($query);
        $sth->execute();

        return $sth->fetch();
    }
}
