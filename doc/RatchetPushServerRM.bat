cd %~dp0
cd ..\public

chcp 65001

SET CAKEPHP_ENV=redmine

php bin/push-server.php
pause