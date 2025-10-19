<?php

use Livewire\Volt\Volt;

it('can render', function () {
    $component = Volt::test('legal.privacy');

    $component->assertSee('');
});
