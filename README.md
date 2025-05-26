# This is my package vuedata

[![Latest Version on Packagist](https://img.shields.io/packagist/v/itstudioat/vuedata.svg?style=flat-square)](https://packagist.org/packages/itstudioat/vuedata)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/itstudioat/vuedata/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/itstudioat/vuedata/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/itstudioat/vuedata.svg?style=flat-square)](https://packagist.org/packages/itstudioat/vuedata)

With this package you may read and update the variables of a vue-file.

For example you have follwing block in an Vue-File

```bash
    data() {
        return {
            testing: 0,
            hpm: {
                name: "Homepage-Structure",
                type: "homepage",
                component: "App"
            }
        }
    }
```

You can read these variables into an php-array.
After changing some variables you may update them in the vue-file.


## Installation

You can install the package via composer:

```bash
composer require itstudioat/vuedata
```

Now you have a ServiceClass named VuedataService with two methods:
- read
- write

## Usage

Here is an example for usage:

```bash
...
use Itstudioat\Vuedata\Services\VuedataService;
...

$vuedataService = new VuedataService();
$array =  $vuedataService->read('vendor/hpm/js/pages/pv_homepage/App.vue');
```

Now you have all variables of the App.vue-file in the $array.
You can manipulate the array, change and add variables.
If you erase variables they will be leaved in the output.
Now you can write the new variables into the vue-file.

```bash
...
use Itstudioat\Vuedata\Services\VuedataService;
...


$data = [
    'hpm' => $array['hpm'],
    'new_test' => 17,
    'homepageStore' => $array['homepageStore'],
];

$answer = $vuedataService->write('vendor/hpm/js/pages/pv_homepage/App.vue', $data);
return $answer;
```

Programmed with support from chatgpt.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Guenther Kron](https://github.com/itstudioat)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
