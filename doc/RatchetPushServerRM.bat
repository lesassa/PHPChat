cd %~dp0
cd ..\public

SET CAKEPHP_ENV=redmine

php bin/push-server.php
pause