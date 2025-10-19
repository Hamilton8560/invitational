<?php

use Livewire\Volt\Volt;

it('can render', function () {
    $component = Volt::test('events.purchase-tickets');

    $component->assertSee('');
});
