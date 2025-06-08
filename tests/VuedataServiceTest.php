<?php

use Itstudioat\Vuedata\Enums\VuedataResult;
use Itstudioat\Vuedata\Services\VuedataService;


it('can read a vue-file', function () {
    //    $path_original = __DIR__ . "/vue/App_Original.vue";
    $path = __DIR__ . "/vue/App.vue";

    //  copy($path_original, $path);


    $vuedataService = new VuedataService();
    $result = $vuedataService->read($path);

    expect($result)->toBeArray();
    expect($result['data']['hpm']['name'])->toBe("Homepage-Struktur");
    expect($result['data']['hpm']['elements']['header']['is_active']['value'])->toBeTrue();
    expect($result['data']['hpm']['elements']['footer']['is_active']['value'])->toBeTrue();
});

it('can not read a vue-file, wrong file-name', function () {
    $path = __DIR__ . "/vue/Appx.vue";
    $vuedataService = new VuedataService();
    $result = $vuedataService->read($path);
    expect($result)->toBeArray();
    expect($result['success'])->toBe(false);
    expect($result['error'])->toBe(VuedataResult::FILE_NOT_EXISTS->value);
});


it('can not read a vue-file, no data-block', function () {
    $path = __DIR__ . "/vue/AppNoDataBlock.vue";
    $vuedataService = new VuedataService();
    $result = $vuedataService->read($path);
    expect($result)->toBeArray();
    expect($result['success'])->toBe(false);
    expect($result['error'])->toBe(VuedataResult::NOT_DATA_BLOCK->value);
});


it('can write a vue-file', function () {
    $path = __DIR__ . "/vue/App.vue";
    $vuedataService = new VuedataService();
    $result = $vuedataService->read($path);

    $result['data']['hpm']['name'] = "Homepage-Struktur changed";
    $result['data']['hpm']['elements']['header']['is_active']['value'] = false;
    $result['data']['hpm']['elements']['footer']['is_active']['value'] = false;

    $result =  $vuedataService->write($path, ['hpm' => $result['data']['hpm']]);

    expect($result)->toBeArray();
    expect($result['status'])->toBe('success');


    $result = $vuedataService->read($path);
    expect($result)->toBeArray();
    expect($result['data']['hpm']['name'])->toBe("Homepage-Struktur changed");
    expect($result['data']['hpm']['elements']['header']['is_active']['value'])->toBeFalse();
    expect($result['data']['hpm']['elements']['footer']['is_active']['value'])->toBeFalse();
});
