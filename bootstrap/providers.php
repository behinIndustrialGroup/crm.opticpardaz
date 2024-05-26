<?php

use BehinInit\BehinInitProvider;
use BehinUserRoles\UserRolesServiceProvider;
use UserProfile\UserProfileProvider;

return [
    App\Providers\AppServiceProvider::class,
    BehinInitProvider::class,
    UserRolesServiceProvider::class,
    UserProfileProvider::class
];
