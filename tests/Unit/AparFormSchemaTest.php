<?php

use App\Support\AparFormSchema;

test('all APAR repeatable sections can be populated by the form', function () {
    $steps = AparFormSchema::steps();
    $emptyData = AparFormSchema::emptyData();
    $tableBlocks = [];

    foreach ($steps as $step) {
        foreach ($step['blocks'] as $block) {
            if ($block['type'] === 'table') {
                $tableBlocks[] = $block;
            }
        }
    }

    expect($tableBlocks)->not->toBeEmpty();

    foreach ($tableBlocks as $block) {
        expect(AparFormSchema::tableFields($block['key']))->toEqual($block['fields']);
        expect(data_get($emptyData, $block['key']))->toBe([]);
    }
});
