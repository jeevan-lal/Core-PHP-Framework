<?php namespace Ctechhindi\CorePhpFramework;

use Slim\Views\PhpRenderer;
use CTH\Config\Layout;

/**
 * Application Base Controller
 */

class BaseController
{
    public $view;

    public function __construct() {
        $this->view = new PhpRenderer(APATH.Layout::$viewsFolderPath);
        $this->view->setLayout(Layout::$layoutFilePath);
    }

    /**
     * Set Page Title
     */
    public static function setTitle($title = "") {
        return Layout::$titlePrefix.$title.Layout::$titleSuffix;
    }
}