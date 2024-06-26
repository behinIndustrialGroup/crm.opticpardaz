1-  add "BehinProcessMaker\\": "packages/behin-process-maker/src/" to composer.json in root dir.

2-  open terminal in root dir and run composer dump-autoload.

2-  for laravel = 9
        add BehinProcessMaker\BehinProcessMakerProvider::class to config/app.php in providers.
    for laravel = 11
        add BehinProcessMaker\BehinProcessMakerProvider::class to bootstrap/providers.php.

3-  php artisan migrate.

4-  add 'pm_username', 'pm_user_password', 'pm_user_access_token', 'pm_user_access_token_exp_date' to user.php model.

5-  add belows to .env file
        PM_SERVER=
        PM_CLIENT_ID=
        PM_CLIENT_SECRET=
        PM_ADMIN_USER=
        PM_ADMIN_PASS=

