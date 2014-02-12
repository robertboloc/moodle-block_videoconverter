<?php

/**
 * @author Robert Boloc <robert.boloc@urv.cat>
 * @copyright 2014 Servei de Recursos Educatius (http://www.sre.urv.cat)
 */

abstract class entity {

    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }
}