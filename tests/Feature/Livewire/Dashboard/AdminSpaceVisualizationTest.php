<?php

use Livewire\Volt\Volt;

it('can render', function () {
    $component = Volt::test('dashboard.admin-space-visualization');

    $component->assertSee('');
});
