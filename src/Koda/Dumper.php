<?php

namespace Koda;


class Dumper {

    public static function dump($item) {
        if(is_object($item)) {
            if($item instanceof EntityInterface) {
                echo $item->dump();
            }

        } else {
            var_dump($item);
        }
    }
}


function dump() {

}

function drop() {

}