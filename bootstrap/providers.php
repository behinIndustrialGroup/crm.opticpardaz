<?php

use BehinFileControl\BehinFileControlProvider;
use BehinInit\BehinInitProvider;
use BehinProcessMaker\BehinProcessMakerProvider;
use BehinUserRoles\UserRolesServiceProvider;
use UserProfile\UserProfileProvider;

return [
    App\Providers\AppServiceProvider::class,
    BehinInitProvider::class,
    UserRolesServiceProvider::class,
    UserProfileProvider::class,
    BehinProcessMakerProvider::class,
    BehinFileControlProvider::class
];
