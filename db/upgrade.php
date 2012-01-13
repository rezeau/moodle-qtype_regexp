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

    if ($oldversion < 2011022301) {
    /// Define field usecase to be added to question_regexp
        $table = new xmldb_table('question_regexp');
        $field = new xmldb_field('usecase', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'usehint');
        
    /// Conditionally launch add field
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }    
    // savepoint reached
        upgrade_plugin_savepoint(true, 2011022301, 'qtype', 'regexp');
    }
    
    if ($oldversion < 2011102300) {
        // table question_regexp to be renamed to qtype_regexp
        $table = new xmldb_table('question_regexp');

        // Launch rename table
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'qtype_regexp');
        }

        upgrade_plugin_savepoint(true, 2011102300,  'qtype', 'regexp');
    }

    if ($oldversion < 2012010100) {
    /// Rename field "question" on table "qtype_regexp" to "questiontype"
        $table = new xmldb_table('qtype_regexp');
        $field = new xmldb_field('question', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'id');

    /// Launch rename field
        if ($dbman->table_exists($table)) {
        	if ($dbman->field_exists($table, $field)) {
		        $dbman->rename_field($table, $field, 'questionid');
		        upgrade_plugin_savepoint(true, 2012010100,  'qtype', 'regexp');
        	}
        }
    }

    return true;
}
?>