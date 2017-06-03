cd %~dp0
cd ..\public\logs

type nul > "cli-debug.log"
type nul > "cli-error.log"
type nul > "tran.log"
type nul > "debug.log"
type nul > "error.log"
type nul > "queries.log"