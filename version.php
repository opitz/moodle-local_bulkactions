<?php
/**
 * Created by PhpStorm.
 * User: opitz
 * Date: 31/05/18
 * Time: 11:09
 */
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

$plugin = new \stdClass();
$plugin->version    = 2019061200;
$plugin->requires  = 2017050500; // Moodle 3.3
$plugin->component  = 'local_bulkactions'; // Full name of the plugin (used for diagnostics)
$plugin->maturity   = MATURITY_STABLE;
