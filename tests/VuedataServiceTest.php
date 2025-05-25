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
    expect($result['hpm']['name'])->toBe("Homepage-Struktur");
    expect($result['hpm']['elements']['header']['is_active']['value'])->toBeTrue();
    expect($result['hpm']['elements']['footer']['is_active']['value'])->toBeTrue();
});

it('can not read a vue-file, wrong file-name', function () {
    $path = __DIR__ . "/vue/Appx.vue";
    $vuedataService = new VuedataService();
    $response = $vuedataService->read($path);
    expect($response->getContent())->toBe(VuedataResult::FILE_NOT_EXISTS->value);
});


it('can not read a vue-file, no data-block', function () {
    $path = __DIR__ . "/vue/AppNoDataBlock.vue";
    $vuedataService = new VuedataService();
    $response = $vuedataService->read($path);
    expect($response->getContent())->toBe(VuedataResult::NOT_DATA_BLOCK->value);
});


it('can write a vue-file', function () {
    $path = __DIR__ . "/vue/App.vue";
    $vuedataService = new VuedataService();
    $result = $vuedataService->read($path);

    $result['hpm']['name'] = "Homepage-Struktur changed";
    $result['hpm']['elements']['header']['is_active']['value'] = false;
    $result['hpm']['elements']['footer']['is_active']['value'] = false;

    $response =  $vuedataService->write($path, ['hpm' => $result['hpm']]);

    expect($response->status())->toBe(200);
    expect($response->getData(true))->toBe([
        'status' => 'ok',
    ]);

    $result = $vuedataService->read($path);
    expect($result)->toBeArray();
    expect($result['hpm']['name'])->toBe("Homepage-Struktur changed");
    expect($result['hpm']['elements']['header']['is_active']['value'])->toBeFalse();
    expect($result['hpm']['elements']['footer']['is_active']['value'])->toBeFalse();
});
