<?php

namespace App\Http\Controllers;

use App\Events\PurchaseCompleted;
use App\Http\Requests\SaleStatementsRequest;
use App\Http\Requests\SaleStoreRequest;
use App\Http\Requests\SaleUpdateRequest;
use App\Http\Resources\SaleCollection;
use App\Http\Resources\SaleResource;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SaleController extends Controller
{
    public function index(Request $request): SaleCollection
    {
        $sales = Sale::all();

        return new SaleCollection($sales);
    }

    public function store(SaleStoreRequest $request): SaleResource
    {
        $sale = Sale::create($request->validated());

        return new SaleResource($sale);
    }

    public function show(Request $request, Sale $sale): SaleResource
    {
        return new SaleResource($sale);
    }

    public function update(SaleUpdateRequest $request, Sale $sale): SaleResource
    {
        $sale->update($request->validated());

        return new SaleResource($sale);
    }

    public function destroy(Request $request, Sale $sale): Response
    {
        $sale->delete();

        return response()->noContent();
    }

    public function statements(SaleStatementsRequest $request)
    {
        $sale = Sale::create($request->validated());

        PurchaseCompleted::dispatch($sale);

        return new SaleResource($sale);
    }
}
