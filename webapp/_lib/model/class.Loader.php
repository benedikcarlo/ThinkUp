<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Loader.php
 *
 * Copyright (c) 2009-2012 Dwi Widiastuti, Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 *
 * Project-wide Loader
 *
 * Implements lazy loading of ThinkUp classes by registering _autoload method in this class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2012 Dwi Widiastuti, Gina Trapani
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class Loader {

    /**
     * Lookup paths for classes and interfaces
     * @var array
     */
    private static $lookup_path;

    /**
     * Classes whose filename doesn't follow the convention
     * @var array
     */
    private static $special_classes;

    /**
     * Register
     *
     * Registers the autoloader to enable lazy loading
     *
     * @param array $paths Array of additional lookup path strings
     * @return bool
     */
    public static function register(Array $paths=null) {
        self::setLookupPath($paths);
        return spl_autoload_register(array(__CLASS__, "load"));
    }

    /**
     * Unregister
     *
     * Unregisters the autoloader script, disabling lazy loading
     *
     * @return bool
     */
    public static function unregister() {
        self::$lookup_path = null;
        self::$special_classes = null;
        return spl_autoload_unregister(array(__CLASS__, "load"));
    }

    /**
     * Set Lookup Path
     *
     * Establishes lookup paths, including additional paths if provided
     *
     * @param array $paths Array of additional lookup path strings
     */
    private static function setLookupPath(Array $paths = null) {
        // check required constants
        defined("THINKUP_ROOT_PATH") || define("THINKUP_ROOT_PATH", dirname(dirname(dirname(dirname(__FILE__)))) . "/");
        defined("THINKUP_WEBAPP_PATH") || define("THINKUP_WEBAPP_PATH", dirname(dirname(dirname(__FILE__))) . "/");

        // set default lookup paths
        self::$lookup_path = array(
        THINKUP_WEBAPP_PATH . "_lib/model/",
        THINKUP_WEBAPP_PATH . "_lib/controller/",
        THINKUP_WEBAPP_PATH . "_lib/model/exceptions/"
        );

        // set default lookup path for special classes
        self::$special_classes = array(
            "Smarty" => THINKUP_WEBAPP_PATH . "_lib/extlib/Smarty-2.6.26/libs/Smarty.class.php"
            );

            if (isset($paths)) {
                foreach($paths as $path) self::$lookup_path[] = $path;
            }
    }

    /**
     * Add Path
     *
     * Adds another path to crawl for class files
     *
     * @param string $path
     */
    public static function addPath($path) {
        if (!isset(self::$lookup_path)) self::register();
        self::$lookup_path[] = $path;
    }

    /**
     * Get Lookup Path
     *
     * Gets the array of lookup paths
     *
     * @return array
     */
    public static function getLookupPath() {
        return self::$lookup_path;
    }

    /**
     * Get Special Classes
     *
     * Gets the array of special class paths
     *
     * @return array
     */
    public static function getSpecialClasses() {
        return self::$special_classes;
    }

    /**
     * Load
     *
     * The method registered to run on _autoload. When a class is instantiated, this
     * method will be called to look up the class file if the class is not present.
     * The second instantiation of the same class wouldn't call this method.
     *
     * @param string $class
     * @param bool
     */
    public static function load($class) {
        // check if class is already in scope
        if (class_exists($class, false)) return;

        // if class is a standard ThinkUp object or interface
        foreach(self::$lookup_path as $path) {
            $filename = $path . "class." . $class . ".php";
            if (file_exists($filename)) {
                require_once($filename);
                return;
            }

            $filename = $path . "interface." . $class . ".php";
            if (file_exists($filename)) {
                require_once($filename);
                return;
            }

            $filename = $path . $class . ".php";
            if (file_exists($filename)) {
                require_once($filename);
                return;
            }
        }

        // if class is a special class
        if(array_key_exists($class, self::$special_classes)) {
            require_once(self::$special_classes[$class]);
            return;
        }
    }
}