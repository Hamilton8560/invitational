<?php

use Livewire\Volt\Volt;

it('can render', function () {
    $component = Volt::test('events.reserve-banner');

    $component->assertSee('');
});
