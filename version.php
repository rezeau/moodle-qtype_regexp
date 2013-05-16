<?PHP
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
 * Regexp question type version information.
 *
 * @package    qtype_regexp
 * @copyright  1999 onwards Martin Dougiamas {@link http://moodle.com} and Joseph R�zeau moodle@rezeau.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'qtype_regexp';
$plugin->version  = 2013051500;

$plugin->requires = 2013051400;
$plugin->dependencies = array(
    'qbehaviour_regexpadaptivewithhelp'   => 2013040900,
    'qbehaviour_regexpadaptivewithhelpnopenalty'  => 2013040900,
);

$plugin->release = '2.5.0 for Moodle 2.5+';
$plugin->maturity  = MATURITY_STABLE;
