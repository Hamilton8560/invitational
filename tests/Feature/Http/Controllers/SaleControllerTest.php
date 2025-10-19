<?php

namespace Tests\Feature\Http\Controllers;

use App\Events\PurchaseCompleted;
use App\Models\Event;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event as EventFacade;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\SaleController
 */
final class SaleControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $sales = Sale::factory()->count(3)->create();

        $response = $this->get(route('sales.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }

    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\SaleController::class,
            'store',
            \App\Http\Requests\SaleStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $event = Event::factory()->create();
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $quantity = fake()->numberBetween(-10000, 10000);
        $unit_price = fake()->randomFloat(/** decimal_attributes **/);
        $total_amount = fake()->randomFloat(/** decimal_attributes **/);
        $status = fake()->randomElement(/** enum_attributes **/);
        $purchased_at = Carbon::parse(fake()->dateTime());

        $response = $this->post(route('sales.store'), [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $unit_price,
            'total_amount' => $total_amount,
            'status' => $status,
            'purchased_at' => $purchased_at->toDateTimeString(),
        ]);

        $sales = Sale::query()
            ->where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->where('quantity', $quantity)
            ->where('unit_price', $unit_price)
            ->where('total_amount', $total_amount)
            ->where('status', $status)
            ->where('purchased_at', $purchased_at)
            ->get();
        $this->assertCount(1, $sales);
        $sale = $sales->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }

    #[Test]
    public function show_behaves_as_expected(): void
    {
        $sale = Sale::factory()->create();

        $response = $this->get(route('sales.show', $sale));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }

    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\SaleController::class,
            'update',
            \App\Http\Requests\SaleUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $sale = Sale::factory()->create();
        $event = Event::factory()->create();
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $quantity = fake()->numberBetween(-10000, 10000);
        $unit_price = fake()->randomFloat(/** decimal_attributes **/);
        $total_amount = fake()->randomFloat(/** decimal_attributes **/);
        $status = fake()->randomElement(/** enum_attributes **/);
        $purchased_at = Carbon::parse(fake()->dateTime());

        $response = $this->put(route('sales.update', $sale), [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $unit_price,
            'total_amount' => $total_amount,
            'status' => $status,
            'purchased_at' => $purchased_at->toDateTimeString(),
        ]);

        $sale->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($event->id, $sale->event_id);
        $this->assertEquals($user->id, $sale->user_id);
        $this->assertEquals($product->id, $sale->product_id);
        $this->assertEquals($quantity, $sale->quantity);
        $this->assertEquals($unit_price, $sale->unit_price);
        $this->assertEquals($total_amount, $sale->total_amount);
        $this->assertEquals($status, $sale->status);
        $this->assertEquals($purchased_at->timestamp, $sale->purchased_at);
    }

    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $sale = Sale::factory()->create();

        $response = $this->delete(route('sales.destroy', $sale));

        $response->assertNoContent();

        $this->assertModelMissing($sale);
    }

    #[Test]
    public function statements_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\SaleController::class,
            'statements',
            \App\Http\Requests\SaleStatementsRequest::class
        );
    }

    #[Test]
    public function statements_saves_and_responds_with(): void
    {
        $product = Product::factory()->create();
        $quantity = fake()->numberBetween(-10000, 10000);
        $user = User::factory()->create();

        EventFacade::fake();

        $response = $this->get(route('sales.statements'), [
            'product_id' => $product->id,
            'quantity' => $quantity,
            'user_id' => $user->id,
        ]);

        $sales = Sale::query()
            ->where('product_id', $product->id)
            ->where('quantity', $quantity)
            ->where('user_id', $user->id)
            ->get();
        $this->assertCount(1, $sales);
        $sale = $sales->first();

        $response->assertOk();
        $response->assertJson($sale, 201);

        EventFacade::assertDispatched(PurchaseCompleted::class, function ($event) use ($sale) {
            return $event->sale->is($sale);
        });
    }
}
