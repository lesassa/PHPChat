cd %~dp0
cd ..\public

SET CAKEPHP_ENV=redmine

bin/cake bot talkTime
pause