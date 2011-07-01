REGEXP QUESTION TYPE plugin for Moodle
by Joseph Rézeau and Jean-Michel Vedrine
VERSION 2.2 compatible with Moodle 1.8 upwards only
this readme datestamp 17:27 30/12/2007
--------------------------------------------------------
INTRODUCTION

This question type (for the Moodle quiz module) aims at a more advanced system of student's response analysis, through the use of regular expresssions. The new question type is an extension of the SHORTANSWER type. It can be used on its own and can also be incorporated into the existing Cloze (embedded) question type, by being coded as {REGEXP} (on the model of {SHORTANSWER}.

DOCUMENTATION

See help file /lang/en_utf8/help/quiz/regexp.html

INSTALLATION
Unzip regexp.zip and copy the entire regexp folder into your moodle/question/type/ folder. 
When in your Moodle site, login as Admin and visit the Notifications page once for the new regexp question type to be registered in the database. You should see these messages (example for a mySql database):
*******************************
regexp plugin needs upgrading
(mysql): CREATE TABLE mdl_question_regexp ( id int(10) unsigned NOT NULL auto_increment, question int(10) unsigned NOT NULL default '0', answers varchar(255) NOT NULL default '', PRIMARY KEY (id), KEY question (question)) TYPE=MyISAM COMMENT='Options for regexp questions'  
Success
(mysql): SELECT name FROM mdl_config WHERE name = 'qtype_regexp_version'   (mysql): UPDATE mdl_config SET value = '2006032200' WHERE name = 'qtype_regexp_version'  
regexp tables have been set up correctly
***************************

PLease note that this may not work with databases other than mySql, because I know nothing about them...
Please report all bugs to moodle@rezeau.org

********************************************************

NOTE.- Integration with MULTIANSWER (=cloze, embedded) question type
If you want to integrate regexp question type with MULTIANSWER, you have to overwrite \question\type\multianswer\questiontype.php with the replacement file that can be downloaded from the moodle Modules and plugins Question Type: Regular Expression Short Answer page at: http://moodle.org/mod/data/view.php?d=13&rid=338

You have to be extra careful when integrating a REGEXP sub-question within a MULTIANSWER (Cloze) question. You should try it out as a standalone REGEXP question first, and then copy all the relevant answer fields into the Cloze question. Here is an example. Please note that the very first answer must be the best correct answer and must NOT be a regular expression.
---
The word Moodle was originally an {:REGEXP:~%100%acronym#Excellent!~%0%--.*nym$#Your answer should end in “-nym”.~%0%--[aeiou].*#The missing word should start with a vowel (because it's preceded by the indefinite article an).~%0%--^acro.*#The missing word starts with the root-word acro, from the Greek akros "tip, end".~%0%.*#anything goes} for Modular Object-Oriented Dynamic Learning Environment, which is mostly useful to programmers and education theorists.
---

Joseph

*******
HISTORY
*******

30 DECEMBER 2007 - JR
- re-wrote this readme.txt file to be compliant with more recent version
- put the \question\type\multianswer\questiontype.php replacement files for 1.8 and 1.9 in the modules and plugins page

28 MARCH 2007 - JR
made compatible with latest Moodle 1.8 version

28 FEBRUARY 2007 - JR
added missing Insert Image drop-down list in question edit window

18 FEBRUARY 2007 - JR
New features
a) an optional Hint button to allow student to "buy" a letter
b) an automatic generation of alternate correct answers based on the regular expressions entered by the teacher, which are displayed in Question Preview mode
fully compatible with Moodle 1.8.1+

28 AUGUST 2006 - JR

modified file question/type/multianswer/questiontype.php:

* enabled HTML formatting inside feedback popup window of cloze questions
* enabled keeping of backslashes inside cloze question answer field for compatibility with regexp analysis
* corrected the apostrophe bugs

24 AUGUST 2006 - JR

* removed unused "useregexp" field from database

* based \question\type\regexp\questiontype.php on latest version of \question\type\shortanswer\questiontype.php for Moodle 1.8.1+, i.e.:
// $Id: readme.txt,v 1.1.2.1 2007/12/30 17:15:15 joseph_rezeau Exp $

* solved the diacritic (French accents) and the apostrophe bugs in student's answer analysis

05 AUGUST 2006 - JR

made compliant with latest version of Moodle 1.7.1

11 JUNE 2006 - JR

corrected wrong names of postgre7.php and postgre7.sql to -> postgres7.php and postgres7.sql

**************************************
PS.- To see it in action, go to http://rezeau.info/moodle and login as user moodler, password moodler
for a teacher password, contact moodle@rezeau.org