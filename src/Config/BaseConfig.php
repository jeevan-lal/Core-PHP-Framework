<?php namespace Ctechhindi\CorePhpFramework\Config;

use CTH\Config\Boot;

class BaseConfig
{
    /**
     * Application Boot Mode
     */
    protected $bootMode;

    public function __construct() {
        $this->bootMode = Boot::$mode;
    }

    /**
     * Get Application Config
     */
    public function get($name) {

        if ($this->bootMode === "production") {
            return $this->production[$name];
        } else {
            return $this->development[$name];
        }
    }
}