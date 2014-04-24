<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @author    Robert Boloc <robert.boloc@urv.cat>
 * @copyright 2014 Servei de Recursos Educatius (http://www.sre.urv.cat)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/entity.php');

class file extends entity {

    /**
     * Creates a new file record.
     *
     * @param object $file
     * @return int|boolean
     */
    public function create_file_record($file) {
        return $this->db->insert_record('block_vc_files', $file);
    }

    /**
     * Removes a file record from the database.
     *
     * @param type $id
     * @return boolean
     */
    public function delete_file_record($id) {
        return $this->db->delete_records('block_vc_files', array(
            'id' => $id,
        ));
    }
}
