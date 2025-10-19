<?php

use Livewire\Volt\Volt;

it('can render', function () {
    $component = Volt::test('dashboard.my-purchases');

    $component->assertSee('');
});
