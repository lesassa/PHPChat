cd %~dp0
cd ..\public

SET CAKEPHP_ENV=com

php bin/push-server.php
pause