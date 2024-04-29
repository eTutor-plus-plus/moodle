# Download Moodle
Invoke-WebRequest -Uri "https://download.moodle.org/download.php/direct/stable404/moodle-4.4.zip" -OutFile "moodle.zip"
Expand-Archive -Path "moodle.zip" -DestinationPath "."
Rename-Item -Path "moodle" -NewName "wwwroot"
Remove-Item "moodle.zip"

# Download CodeRunner
Invoke-WebRequest -Uri "https://moodle.org/plugins/download.php/29972/qtype_coderunner_moodle43_2023090800.zip" -OutFile "coderunner.zip"
Invoke-WebRequest -Uri "https://moodle.org/plugins/download.php/25541/qbehaviour_adaptive_adapted_for_coderunner_moodle43_2021112300.zip" -OutFile "coderunner_qb.zip"
Expand-Archive -Path "coderunner.zip" -DestinationPath "./wwwroot/question/type/"
Expand-Archive -Path "coderunner_qb.zip" -DestinationPath "./wwwroot/question/behaviour/"
Remove-Item "coderunner.zip"
Remove-Item "coderunner_qb.zip"
