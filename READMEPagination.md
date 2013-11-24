
=======
Molajo Pagination
=======

[![Build Status](https://travis-ci.org/Molajo/Pagination.png?branch=master)](https://travis-ci.org/Molajo/Pagination)

Data object ArrayIterator and easy pagination for PHP, framework independent.

## At a glance ...

1. Get the `$page_url`, `query parameters` and `page` value from your preferred `Http Request Class.`
1. Run a query (or produce a list of items) using normal `offset` and `row limit` criteria.
2. Instantiate the `Pagination Adapter`, injecting it with the database query results and various pagination values.
3. Use the pagination object when rendering content and pagination user interface.

```php

    /** From Http Request Class */
    $page_url = 'http://example.com/staff';
    $query_parameters = array('tag' => 'celebrate'); // Exclude the page parameter
    $page = 1;

    /** From Database Query */
    $data = $this->database->execute($query);
    $total_items = 15;

    /** Application Configuration */
    $per_page = 3;          // How many items should display on the page?
    $display_links = 3;     // How many numeric page links should display in the pagination?

    /** Instantiate the Pagination Adapter */
    use Molajo\Pagination\Adapter as Pagination;
    $pagination = new Pagination(
        $data,
        $page_url,
        $query_parameters,
        $total_items,
        $per_page,
        $display_links,
        $page
        );

    /** Data: use pagination object as ArrayIterator */
    foreach ($pagination as $item) {
        include __DIR__ . '/' . 'TemplatePost.php';
    }

    /** Pagination: getPageUrl, getStartDisplayPage and getStopDisplayPage methods */
    <footer class="pagination">
        <a href="<?php echo $pagination->getPageUrl('first'); ?>">First</a>
        &nbsp;<a href="<?php echo $pagination->getPageUrl('prev'); ?>">«</a>
        <?php
        for ($i = $pagination->getStartDisplayPage(); $i < $pagination->getStopDisplayPage(); $i++) { ?>
            <a href="<?php echo $pagination->getPageUrl($i); ?>"><?php echo $i; ?></a>
        <?php
        } ?>
        <a href="<?php echo $pagination->getPageUrl('next'); ?>">»</a>
        &nbsp;<a href="<?php echo $pagination->getPageUrl('last'); ?>">Last</a>
    </footer>
```


### Working Example

A working example of a blog page in the [.dev/Example folder](https://github.com/Molajo/Pagination/tree/master/.dev/Example).
It demonstrates how to use the pagination with an Http Request and database simulation. You
don't have to hook it up to your database, the example works right out of the box on your
localhost with no setup. The code is well documented in order to help you get up and running
quickly.


There are only a few commands. The first displays the Url for the page specified. A numeric
page number can be provided, or one of the following literals: first, prev, current, next, and last.
```php
    <a href="<?php echo $pagination->getPageUrl('first'); ?>">First</a>
```

The other two commands are used in the loop to display the numeric page links between the
First and Previous and the Next and Last. Basically, the command is saying process this loop
from the start value to the stop value.

```php
    for ($i = $pagination->getStartDisplayPage(); $i < $pagination->getStopDisplayPage(); $i++) {
```

And, that's all there is to it. You can style your output, as desired.

```php
    <footer class="pagination">
        <a href="<?php echo $pagination->getPageUrl('first'); ?>">First</a>
        &nbsp;<a href="<?php echo $pagination->getPageUrl('prev'); ?>">«</a>
        <?php
        for ($i = $pagination->getStartDisplayPage(); $i < $pagination->getStopDisplayPage(); $i++) { ?>
            <a href="<?php echo $pagination->getPageUrl($i); ?>"><?php echo $i; ?></a>
        <?php
        } ?>
        <a href="<?php echo $pagination->getPageUrl('next'); ?>">»</a>
        &nbsp;<a href="<?php echo $pagination->getPageUrl('last'); ?>">Last</a>
    </footer>
```

## Install using Composer from Packagist

### Step 1: Install composer in your project

```php
    curl -s https://getcomposer.org/installer | php
```

### Step 2: Create a **composer.json** file in your project root

```php
{
    "require": {
        "Molajo/Pagination": "1.*"
    }
}
```

### Step 3: Install via composer

```php
    php composer.phar install
```

## Requirements and Compliance
 * PHP framework independent, no dependencies
 * Requires PHP 5.3, or above
 * [Semantic Versioning](http://semver.org/)
 * Compliant with:
    * [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md) and [PSR-1](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md) Namespacing
    * [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) Coding Standards
 * [phpDocumentor2] (https://github.com/phpDocumentor/phpDocumentor2)
 * [phpUnit Testing] (https://github.com/sebastianbergmann/phpunit)
 * Author [AmyStephen](http://twitter.com/AmyStephen)
 * [Travis Continuous Improvement] (https://travis-ci.org/profile/Molajo)
 * Listed on [Packagist] (http://packagist.org) and installed using [Composer] (http://getcomposer.org/)
 * Use github to submit [pull requests](https://github.com/Molajo/Pagination/pulls) and [features](https://github.com/Molajo/Pagination/issues)
 * Licensed under the MIT License - see the `LICENSE` file for details
