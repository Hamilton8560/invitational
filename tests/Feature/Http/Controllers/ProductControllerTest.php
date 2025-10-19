<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Event;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\ProductController
 */
final class ProductControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_displays_view(): void
    {
        $products = Product::factory()->count(3)->create();

        $response = $this->get(route('products.index'));

        $response->assertOk();
        $response->assertViewIs('product.index');
        $response->assertViewHas('products');
    }

    #[Test]
    public function create_displays_view(): void
    {
        $response = $this->get(route('products.create'));

        $response->assertOk();
        $response->assertViewIs('product.create');
    }

    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\ProductController::class,
            'store',
            \App\Http\Requests\ProductStoreRequest::class
        );
    }

    #[Test]
    public function store_saves_and_redirects(): void
    {
        $event = Event::factory()->create();
        $type = fake()->randomElement(/** enum_attributes **/);
        $name = fake()->name();
        $price = fake()->randomFloat(/** decimal_attributes **/);
        $current_quantity = fake()->numberBetween(-10000, 10000);

        $response = $this->post(route('products.store'), [
            'event_id' => $event->id,
            'type' => $type,
            'name' => $name,
            'price' => $price,
            'current_quantity' => $current_quantity,
        ]);

        $products = Product::query()
            ->where('event_id', $event->id)
            ->where('type', $type)
            ->where('name', $name)
            ->where('price', $price)
            ->where('current_quantity', $current_quantity)
            ->get();
        $this->assertCount(1, $products);
        $product = $products->first();

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('product.id', $product->id);
    }

    #[Test]
    public function show_displays_view(): void
    {
        $product = Product::factory()->create();

        $response = $this->get(route('products.show', $product));

        $response->assertOk();
        $response->assertViewIs('product.show');
        $response->assertViewHas('product');
    }

    #[Test]
    public function edit_displays_view(): void
    {
        $product = Product::factory()->create();

        $response = $this->get(route('products.edit', $product));

        $response->assertOk();
        $response->assertViewIs('product.edit');
        $response->assertViewHas('product');
    }

    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\ProductController::class,
            'update',
            \App\Http\Requests\ProductUpdateRequest::class
        );
    }

    #[Test]
    public function update_redirects(): void
    {
        $product = Product::factory()->create();
        $event = Event::factory()->create();
        $type = fake()->randomElement(/** enum_attributes **/);
        $name = fake()->name();
        $price = fake()->randomFloat(/** decimal_attributes **/);
        $current_quantity = fake()->numberBetween(-10000, 10000);

        $response = $this->put(route('products.update', $product), [
            'event_id' => $event->id,
            'type' => $type,
            'name' => $name,
            'price' => $price,
            'current_quantity' => $current_quantity,
        ]);

        $product->refresh();

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('product.id', $product->id);

        $this->assertEquals($event->id, $product->event_id);
        $this->assertEquals($type, $product->type);
        $this->assertEquals($name, $product->name);
        $this->assertEquals($price, $product->price);
        $this->assertEquals($current_quantity, $product->current_quantity);
    }

    #[Test]
    public function destroy_deletes_and_redirects(): void
    {
        $product = Product::factory()->create();

        $response = $this->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'));

        $this->assertSoftDeleted($product);
    }

    #[Test]
    public function statements_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\ProductController::class,
            'statements',
            \App\Http\Requests\ProductStatementsRequest::class
        );
    }

    #[Test]
    public function statements_saves_and_redirects(): void
    {
        $type = fake()->randomElement(/** enum_attributes **/);
        $name = fake()->name();
        $price = fake()->randomFloat(/** decimal_attributes **/);
        $event = Event::factory()->create();

        $response = $this->get(route('products.statements'), [
            'type' => $type,
            'name' => $name,
            'price' => $price,
            'event_id' => $event->id,
        ]);

        $products = Product::query()
            ->where('type', $type)
            ->where('name', $name)
            ->where('price', $price)
            ->where('event_id', $event->id)
            ->get();
        $this->assertCount(1, $products);
        $product = $products->first();

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('product.id', $product->id);
    }
}
