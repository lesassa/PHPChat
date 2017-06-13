cd %~dp0
cd ..\public

SET CAKEPHP_ENV=redmine

bin/cake server -H 172.19.118.45
