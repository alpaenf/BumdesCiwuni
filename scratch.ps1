(Get-Content .env) -replace 'APP_LOCALE=.*', 'APP_LOCALE=id' | Set-Content .env
