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
    const USR_TYPE_FB_AUTHORIZED = 3;

    //Types of posts
    const POST_TYPE_CREATED = 1;
    const POST_TYPE_IMPORTED = 2;

    //Types of content
    const CONTENT_TYPE_ARTICLE = 1;
    const CONTENT_TYPE_NEWS = 2;
    const CONTENT_TYPE_PHOTO = 3;
    const CONTENT_TYPE_VIDEO = 4;
    const CONTENT_TYPE_VOTING = 5;
    const CONTENT_TYPE_POST = 6;

    //Statuses of objects (enabled/disabled/suspended)
    const STATUS_DISABLED = 0;
    const STATUS_SUSPENDED = 1;
    const STATUS_ENABLED = 2;
    const STATUS_DELETED = -1;
    const STATUS_IN_STOCK = 4;

    //Types of refreshing indexes
    const IND_R_CONTENT = 1;
    const IND_R_IMAGES = 2;
    const IND_R_ANSWERS = 3;
    const IND_R_CATEGORIES = 4;
    const IND_R_COMMENTS = 5;
    const IND_R_ALL = 6;

    //Types of placement
    const KIND_INTERESTING_CONTENT = 1;
    const KIND_INTERESTING_COMMENTS = 2;
    const KIND_FORUM = 3;
    const KIND_NOT_SELECTED = 4;

    //Types of banners
    const BANNER_TYPE_IMAGE = 1;
    const BANNER_TYPE_CODE = 2;

    //Recommendations reason types
    const OFFER_REASON_AUTHOR = 1;
    const OFFER_REASON_GROUP = 2;
    const OFFER_REASON_CAT_TAG = 3;

    //Post FB types
    const FB_POST_PHOTO = "photo";
    const FB_POST_VIDEO = "video";
    const FB_POST_STATUS = "status";
    const FB_POST_LINK = "link";
    const FB_POST_EVENT = "event";

    //Sticky status
    const STICKY_MAIN = 1;
    const STICKY_CATEGORIES = 2;
    const STICKY_ANY = 3;
    const STICKY_NONE = 4;
}