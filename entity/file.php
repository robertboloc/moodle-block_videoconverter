<?php

/**
 * @author Robert Boloc <robert.boloc@urv.cat>
 * @copyright 2014 Servei de Recursos Educatius (http://www.sre.urv.cat)
 */

require_once __DIR__ . '/entity.php';

class file extends entity {

     public function create_file_record($file) {
         return $this->db->insert_record('block_vc_files', $file);
     }

     public function delete_file_record($id) {
         return $this->db->delete_records('block_vc_files', array(
             'id' => $id,
         ));
     }
}