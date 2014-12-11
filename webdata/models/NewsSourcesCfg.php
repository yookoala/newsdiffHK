<?php

class NewsSourcesCfg {
    static $cfg = array();
    static $parser_callbacks = array();

    static function setAll($cfg) {
        self::$cfg = $cfg;
        self::$parser_callbacks = array(); // reset cache
    }
    static function getAll() {
        return self::$cfg;
    }
    static function get($id) {
        if (!isset(self::$cfg[$id])) return NULL;
        return self::$cfg[$id];
    }

    /**
     * returns the parser callback for a host
     */
    static function getHostParser($host) {
        if (isset(self::$parser_callbacks[$host])) {
            return self::$parser_callbacks[$host];
        }
        foreach (self::$cfg as $id => $source ) {
            foreach ((array) $source['parsers'] as $parser_host => $parser_method) {
                if ($parser_host == $host) {
                    self::$parser_callbacks[$host] = array($source['class'], $parser_method);
                    break;
                }
            }
            if (isset(self::$parser_callbacks[$host])) return self::$parser_callbacks[$host];
        }
        return FALSE; // there is no match
    }

    /**
     * get crawlers
     */
    static function getCrawlers() {
        $crawlers = array();
        foreach (self::$cfg as $id => $source) {
            $crawlers[$id] = $source['class'];
        }
        return $crawlers;
    }

    /**
     * get crawlers
     */
    static function getNames() {
        $crawlers = array();
        foreach (self::$cfg as $id => $source) {
            $crawlers[$id] = $source['name'];
        }
        return $crawlers;
    }

}
