---------------------------------------
REGEXP QUESTION TYPE plugin for Moodle
by Joseph Rézeau and Jean-Michel Vedrine
----------------------------------------

-------------------------------
How to install on a moodle site
-------------------------------
If you downloaded this zip archive from the new moodle.org plugins page
1.- Unzip the zip archive to your local computer.
2.- This will give you a folder named "regexp".
3.- GO TO STEP 4 below
---
If you downloaded this zip archive from https://github.com/rezeau
1.- Unzip the zip archive to your local computer.
2.- This will give you a folder named something like "rezeau-moodle_qtype_regexp-ff8c6a1". The end of the name may vary.
3.- ***Rename*** that folder to "regexp".
---
4.- Upload the regexp folder to <your moodle>/question/type/ folder.
5.- Visit your Admin/Notifications page so that the new question type gets installed.


This question type (for the Moodle quiz module) aims at a more advanced system of student's response analysis, through the use of regular expresssions. The new question type is an extension of the SHORTANSWER type. It can be used on its own and can also be incorporated into the existing Cloze (embedded) question type (see DOCUMENTATION).

***IMPORTANT***

If you want to take full advantage of the regexp question *Help* feature, which allows the student to "buy" a letter, you'll have to install 2 more plugins on your moodle installation. Those plugins are 2 question behaviours, located at:
https://github.com/rezeau/moodle-qbehaviour_regexpadaptivewithhelp
and
https://github.com/rezeau/moodle-qbehaviour_regexpadaptivewithhelpnopenalty

OR download from moodle's plugin repository, at http://moodle.org/plugins/browse.php?list=category&id=31

Download both and install to your <moodle>/question/behaviour/ folder. 

DOCUMENTATION

http://docs.moodle.org/23/en/Regular_Expression_Short-Answer_question_type

CONTACT

moodle@rezeau.org