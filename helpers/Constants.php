<?php

namespace app\helpers;

class Constants
{
    //Roles of users
    const ROLE_ADMIN = 1;
    const ROLE_REDACTOR = 2;
    const ROLE_REGULAR_USER = 3;

    //Types of users (imported/created)
    const USR_TYPE_CREATED = 1;
    const USR_TYPE_IMPORTED = 2;

    //Statuses of objects (enabled/disabled/suspended)
    const STATUS_DISABLED = 0;
    const STATUS_SUSPENDED = 1;
    const STATUS_ENABLED = 2;
    const STATUS_DELETED = -1;
}