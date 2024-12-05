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
 * Version information for the Regexp question type.
 *
 * @package    qtype_regexp
 * @copyright  2011-2024 Joseph Rézeau moodle@rezeau.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'qtype_regexp';
$plugin->dependencies = [
    'qbehaviour_regexpadaptivewithhelp'   => 2024050200,
    'qbehaviour_regexpadaptivewithhelpnopenalty'  => 2024050200,
];
$plugin->version  = 2024111001;
// Require Moodle 4.2.0.
// Require PHP 8.
$plugin->requires = 2023042400.00;
$plugin->release = '4.5.0';
$plugin->maturity = MATURITY_STABLE;
