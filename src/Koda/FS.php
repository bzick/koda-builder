<?php

namespace Koda;

/**
 * FS helper
 * @package Koda
 */
class FS {

    /**
     * Get contents of the file
     * @param string $path
     * @return string
     * @throws \LogicException
     */
    public static function get($path) {
        if(is_file($path)) {
            return file_get_contents($path);
        } else {
            throw new \LogicException("Fine $path not found");
        }
    }

    /**
     * Put contents of the file
     * @param string $path
     * @param string $content
     * @return int
     */
    public static function put($path, $content) {
        return file_put_contents($path, $content);
    }

    /**
     * Append contents to the file
     * @param string $path
     * @param string $content
     * @return int
     */
    public static function append($path, $content) {
        return file_put_contents($path, $content, FILE_APPEND);
    }
}