FB MEDIA PORTAL
===============

Media portal management system, the content of which is imported from FB 
groups through Adminizator system. Based on [Yii 2](http://www.yiiframework.com/) framework 

REQUIREMENTS
------------

The minimum requirement by this project template that your Web server supports PHP 5.4.0.


INSTALLATION
------------

### Install composer

If you do not have [Composer](http://getcomposer.org/), you may install it by following the instructions at [getcomposer.org](http://getcomposer.org/doc/00-intro.md#installation-nix). If you using [OpenServer](https://ospanel.io/) you shouldn't install composer separately, because it is already integrated.

### Update project

You can then clone or download this project into your preferred directory on your web server. By default - project will be without necessary framework files, so it can't be run by your server. You need update it through [Composer](http://getcomposer.org/), to download all necessary system files. Open your console and go to your project directory using following command.

~~~
cd "your/project/directory"
~~~

Then you can use composer to update your project and download all necessary system files. Run following command

~~~
php composer update
~~~

Wait until project be updated. Then you can run project with your server.


CONFIGURATION
-------------

### Database

Edit the file `config/db.php` with real data, for example:

```php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=yii2basic',
    'username' => 'root',
    'password' => '1234',
    'charset' => 'utf8',
];
```

**NOTES:**
- Yii won't create the database for you, this has to be done manually before you can access it.
- Check and edit the other files in the `config/` directory to customize your application as required.
- Refer to the README in the `tests` directory for information specific to basic application tests.
