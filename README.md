# ReplaceableModel

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

The default Eloquent model is great for most cases, but if you have a database table that has additional constraints you may run into race conditions where the standard update() call will fail.
Imagine, for example, the following table structure:

```
id auto increment
user_id
widget_id
date
```

where you have a constraint with each user can only have one widget per day, so you have a unique constraint across user_id and date.  Now in your interface you have a form that uses ajax calls to update the entries in this table, which may include removing items based on which items are selected.  Because the form submits, say, an entire month's worth of widgets, you don't want to loop through and do individual inserts - you want to perform a single insert query. So you have something like the following in your php code:

``` php
Model::where('user_id',$user_id)->delete();
Model::insert($inserts);
```

This essentially performs the following under the hood:

``` sql
delete from table where user_id = A;
insert into table (user_id, widget_id, date) values (A, B, C) ...
```

If you get stuck in a race condition, you might end up with the following:

``` sql
delete from table where user_id = A; -- process #1
delete from table where user_id = A; -- process #2
insert into table (user_id, widget_id, date) values (A, B, C) ... -- process #1
insert into table (user_id, widget_id, date) values (A, B, C) ... -- process #2 - Exception!
```

The second insert will result in an exception.  If the second query had an additional row than the first one, that insert is lost forever.

Before Laravel, I would normally have handled this type of situation with REPLACE or INSERT IGNORE commands.  REPLACE will do a delete and insert based on any constraints in the query, and INSERT IGNORE will perform the insert but if there are any rows that cause constraint collisions those rows will not be updated.  You would use REPLACE if you want the last query to overwrite any existing rows, and you would use INSERT IGNORE to only insert new rows.

Because this is a specific feature of MySQL, Laravel does not support it in Eloquent.  The standard solution is to perform a raw query.  This is ok, but it is kind of cumbersome to build this query every time with the bindings, etc, and I decided it would be helpful to create a trait for Eloquent that handles all of this for me and can be accessed in the same way that I would use insert().

Note that I'm **NOT** using the Builder class here.  I'm directly extending the Model class and as such you won't be able to chain these functions like you might the regular insert() command.  This is really just a macro to fix a problem that I had.  I welcome any pull requests that solve additional problems you may have with this package.

The 'saving' and 'saved' events **are** fired for both of these commands.

## Install

Via Composer

``` bash
$ composer require jdavidbakr/replaceable-model
```

## Usage

Apply the trait to your models to activate the ability to use replace and insertIgnore

``` php
class model extends Model {
	...	
	use \jdavidbakr\ReplaceableModel\ReplaceableModel
	...
}
```

Then build your insert array like you would for the insert() call and call one of the two functions:

``` php
$inserts = [...];
\App\Model::replace($inserts);
\App\Model::insertIgnore($inserts);
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email me@jdavidbaker.com instead of using the issue tracker.

## Credits

- [J David Baker][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/jdavidbakr/ReplaceableModel.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/jdavidbakr/ReplaceableModel/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/jdavidbakr/ReplaceableModel.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/jdavidbakr/ReplaceableModel.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/jdavidbakr/ReplaceableModel.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/jdavidbakr/ReplaceableModel
[link-travis]: https://travis-ci.org/jdavidbakr/ReplaceableModel
[link-scrutinizer]: https://scrutinizer-ci.com/g/jdavidbakr/ReplaceableModel/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/jdavidbakr/ReplaceableModel
[link-downloads]: https://packagist.org/packages/jdavidbakr/ReplaceableModel
[link-author]: https://github.com/jdavidbakr
[link-contributors]: ../../contributors
