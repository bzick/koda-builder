<?php

namespace Koda;


class FS {

    public static function get($path) {
        return file_get_contents($path);
    }

    public static function put($path, $content) {
        return file_put_contents($path, $content);
    }

    public static function append($path, $content) {
        return file_put_contents($path, $content, FILE_APPEND);
    }
}