![geniem-github-banner](https://cloud.githubusercontent.com/assets/5691777/14319886/9ae46166-fc1b-11e5-9630-d60aa3dc4f9e.png)
# WP Dropin: Better db-error.php
[![Build Status](https://travis-ci.org/devgeniem/better-wp-db-error.svg?branch=master)](https://travis-ci.org/devgeniem/better-wp-db-error) [![Latest Stable Version](https://poser.pugx.org/devgeniem/better-wp-db-error/v/stable)](https://packagist.org/packages/devgeniem/better-wp-db-error) [![Total Downloads](https://poser.pugx.org/devgeniem/better-wp-db-error/downloads)](https://packagist.org/packages/devgeniem/better-wp-db-error) [![Latest Unstable Version](https://poser.pugx.org/devgeniem/better-wp-db-error/v/unstable)](https://packagist.org/packages/devgeniem/better-wp-db-error) [![License](https://poser.pugx.org/devgeniem/better-wp-db-error/license)](https://packagist.org/packages/devgeniem/better-wp-db-error)

This `db-error.php` dropin enhances default "Database connection error page".

* It gives better connection error debugging for administrator when using wp-cli.
* It shows nice error message for end users.
* It returns `503 Service not available` status code instead of `200 OK`
* It shows database connection debugging to frontend when `WP_DEBUG` is used.

## Project Goals
The error message page is **currently only in Finnish** but we have plans for adding other languages as well.

## Installation
You can copy `db-error.php` to your `wp-content` folder. Just plug&play.

OR you can use composer so that you can automatically update it too. Put these in your composer.json:
```json
{
    "require": {
        "devgeniem/better-wp-db-error": "^0.1"
    },
    "extra": {
        "dropin-paths": {
            "htdocs/wp-content/": ["type:wordpress-dropin"],
        }
    }
}
```
## Screenshots

Output from terminal:

<img alt="Generated Error output in command line with wp-cli" src="https://cloud.githubusercontent.com/assets/5691777/16680986/dd3fdcb6-44fa-11e6-9f46-5225412915cb.png" width="500px">

Error Page showed to site visitors:
<img alt="Error page to site visitors (in Finnish)" src="https://cloud.githubusercontent.com/assets/5691777/16680985/dd1d7e28-44fa-11e6-9113-b6374a3835a1.png" width="80%">

## Credits
We used [alexphelps/server-error-pages](http://alexphelps.github.io/server-error-pages/) as base for this.

## License
GPLv3
