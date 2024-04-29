#!/bin/bash

# Download Moodle
wget -O moodle.zip https://download.moodle.org/download.php/direct/stable404/moodle-4.4.zip
unzip moodle.zip
mv moodle wwwroot
rm moodle.zip

# Download CodeRunner
wget -O coderunner.zip https://moodle.org/plugins/download.php/29972/qtype_coderunner_moodle43_2023090800.zip
wget -O coderunner_qb.zip https://moodle.org/plugins/download.php/25541/qbehaviour_adaptive_adapted_for_coderunner_moodle43_2021112300.zip
unzip coderunner.zip -d ./wwwroot/question/type/
unzip coderunner_qb.zip -d ./wwwroot/question/behaviour/
rm coderunner.zip
rm coderunner_qb.zip