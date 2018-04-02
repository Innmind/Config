# Config

| `master` | `develop` |
|----------|-----------|
| [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/Config/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Innmind/Config/?branch=master) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/Config/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/Config/?branch=develop) |
| [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/Config/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Innmind/Config/?branch=master) | [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/Config/badges/coverage.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/Config/?branch=develop) |
| [![Build Status](https://scrutinizer-ci.com/g/Innmind/Config/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Innmind/Config/build-status/master) | [![Build Status](https://scrutinizer-ci.com/g/Innmind/Config/badges/build.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/Config/build-status/develop) |

Library to enforce a configuration schema with an emphasis on schema visualization.

It's born after a constant struggle to build prototype definitions with [`symfony/config`](https://symfony.com/doc/current/components/config.html) as well as the fact prototypes can't be mixed with property definitions.

**Note**: a prototype is a definition allowing to repeat the sub-schema in the array, for example `prototype<int>: bool` allows you to have a configuration like `[1 => true, 2 => false /* etc... */]`.

## Installation

```sh
composer require innmind/config
```

## Usage

To better visualize the config schema you should define it via yaml or json (or any other minimalist format). Here's an example showing the possibilities of this library:

```yaml
# schema.yml
prototype<string>:
    type: string
    alias: '?string'
    repository: '?string'
    factory: '?string'
    labels: set<string>
    identity:
        property: string
        type: string
    properties:
        prototype<string>:
            type: string
            prototype<scalar>: mixed
```

```php
use Innmind\Config\Config;
use Symfony\Component\Yaml\Yaml;

$config = (new Config)->build(Yaml::parseFile('schema.yml'));
$data = $config->process($data);
```

When you call `process` it will validate the `$data` but also transform the data in case of `set`, `stream` and `sequence` types by returning instances of `Innmind\Immutable\Set`, `Innmind\Immutable\Stream` and `Innmind\Immutable\Sequence`, the `$data` returned is also a `Innmind\Immutable\Map` instead of a simple array in order to ease the manipulation of the config data.

The full list of keys formats you can use:

* `prototype<int>` will instruct to have an int as a key
* `prototype<int>+` will instruct to have an int as a key and to have at least one key
* `prototype<string>` will instruct to have an string as a key
* `prototype<string>+` will instruct to have an string as a key and to have at least one key
* `prototype<scalar>` will instruct to have an scalar as a key
* `prototype<scalar>+` will instruct to have an scalar as a key and to have at least one key
* any other string will be considered as a key name

The full list of values formats you can use:

* any value having a `is_{type}` function, this includes `int`, `float`, `bool`, `string`, `scalar`, `array`, `object` and `resource`, can be declared optional by prefixing with a `?`
* `set<{type}>` where `type` is any value accepted by `Innmind\Immutable\Set`
* `set<{type}>+` where `type` is any value accepted by `Innmind\Immutable\Set` and must at least have one value
* `stream<{type}>` where `type` is any value accepted by `Innmind\Immutable\Stream`
* `stream<{type}>+` where `type` is any value accepted by `Innmind\Immutable\Stream` and must at least have one value
* `sequence`
* `sequence+` must have at least one value
* `enum({typeA|typeB})` possible values separated by a `|`
* `?enum({typeA|typeB})` possible values separated by a `|` but the value is optional

## Extend behaviour

The formatsshowed are the one by defaults but you can easily yours, for that you need to implements either [`Structure`](src/Structure.php) or [`Property`](src/Property.php) then to use it:

```php
$config = new Config(
    new Structures(
        ...Structures::defaults()->add(MyStructure::class)
    ),
    new Properties(
        ...Properties::defaults()->add(MyProperty::class)
    )
);
```
