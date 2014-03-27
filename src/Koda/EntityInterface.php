<?php
/**
 * @author Ivan Shalganov <bzick@megagroup.ru>
 * @created 26.03.14 20:02
 * @copyright megagroup.ru, 2014
 */

namespace Koda;


interface EntityInterface {

    public function dump($tab = "");
    public function __toString();
}