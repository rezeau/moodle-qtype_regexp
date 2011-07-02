REGEXP QUESTION TYPE plugin for Moodle
by Joseph Rézeau and Jean-Michel Vedrine
VERSION for Moodle 1.9.x
--------------------------------------------------------
INTRODUCTION

This question type (for the Moodle quiz module) aims at a more advanced system of student's response analysis, through the use of regular expresssions. The new question type is an extension of the SHORTANSWER type. It can be used on its own and can also be incorporated into the existing Cloze (embedded) question type, by being coded as {REGEXP} (on the model of {SHORTANSWER}.

DOCUMENTATION

http://docs.moodle.org/20/en/Regular_Expression_Short-Answer_question_type

INSTALLATION
The zip file you download from my GITHUB repository will be named something like:
rezeau-moodle-qtype_regexp-cf1894e.zip (the last chunk of letters and figures may vary).
Unzip that zip file, you'll get a folder of the same name (rezeau-moodle-qtype_regexp-cf1894e).
***Rename*** that folder to regexp and copy that regexp folder into your moodle 19 moodle/question/type/ folder. 
When in your Moodle site, login as Admin and visit the Notifications page once for the new regexp question type to be registered in the database. You should see these messages (example for a mySql database):
*******************************
regexp plugin needs upgrading
[...]
regexp tables have been set up correctly
***************************
PLease note that this may not work with databases other than mySql, because I know nothing about them...
Please report all bugs to moodle@rezeau.org

********************************************************

NOTE.- Integration with MULTIANSWER (=cloze, embedded) question type
If you want to integrate regexp question type with MULTIANSWER, you have to overwrite \question\type\multianswer\questiontype.php with the replacement file that can be downloaded from the moodle Modules and plugins Question Type: Regular Expression Short Answer page at: http://moodle.org/mod/data/view.php?d=13&rid=338

You have to be extra careful when integrating a REGEXP sub-question within a MULTIANSWER (Cloze) question. You should try it out as a standalone REGEXP question first, and then copy all the relevant answer fields into the Cloze question. Here is an example. Please note that the very first answer must be the best correct answer and must NOT be a regular expression.
---
The word Moodle was originally an {:REGEXP:~%100%acronym#Excellent!~%0%--.*nym$#Your answer should end in "-nym".~%0%--[aeiou].*#The missing word should start with a vowel (because it's preceded by the indefinite article an).~%0%--^acro.*#The missing word starts with the root-word acro, from the Greek akros "tip, end".~%0%.*#anything goes} for Modular Object-Oriented Dynamic Learning Environment, which is mostly useful to programmers and education theorists.
---

Joseph

PS.- To see it in action on a moodle 1.9 test site, go to http://rezeau.info/moodle and login as user moodler, password moodler
for a teacher password, contact moodle@rezeau.org