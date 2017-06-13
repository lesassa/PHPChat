cd %~dp0
cd ..\public

SET CAKEPHP_ENV=development

php bin/push-server.php
pause