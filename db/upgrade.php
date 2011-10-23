<?php  // $Id: upgrade.php,v 1.2 2011/03/01 10:51:06 joseph_rezeau Exp $

// This file keeps track of upgrades to 
// the regexp qtype plugin
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the methods of database_manager class
//
// Please do not forget to use upgrade_set_timeout()
// before any action that may take longer time to finish.

function xmldb_qtype_regexp_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();
    $result = true;

/// And upgrade begins here. For each one, you'll need one 
/// block of code similar to the next one. Please, delete 
/// this comment lines once this file start handling proper
/// upgrade code.

if ($oldversion < 2011022301) {
    /// Define field usecase to be added to question_regexp
        $table = new xmldb_table('question_regexp');
        $field = new xmldb_field('usecase', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'usehint');
        
    /// Conditionally launch add field single
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }	
    // savepoint reached
        upgrade_plugin_savepoint(true, 2011022301, 'qtype', 'regexp');
    }
    return true;
}
?>