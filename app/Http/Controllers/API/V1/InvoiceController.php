<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;

use App\Models\Invoice;
use App\Http\Requests\V1\StoreInvoiceRequest;
use App\Http\Requests\V1\UpdateInvoiceRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\InvoiceResource;
use App\Http\Resources\V1\InvoiceCollection;
use App\Filter\V1\InvoiceFilter;
use App\Http\Requests\V1\BulkStoreInvoiceRequest;


class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!$request->user()->tokenCan('read')) {
        abort(403, 'Unauthorized');
        }

        $filter = new InvoiceFilter();
        $filterItems = $filter->transform($request); //[['column','operator','value']]
        $includePayment = $request->query('includePayment');

        $invoice = Invoice::where($filterItems);

        if($includePayment){
            $invoice = $invoice->with('payments');
        }

        return new InvoiceCollection($invoice->paginate()->appends($request->query()));
            
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInvoiceRequest $request)
    {
        $validated = $request->validated();

        $invoice = Invoice::create([
            'customer_id' => $validated['customerId'],
            'amount' => $validated['amount'],
            'status' => $validated['status'],
            'billed_date' => $validated['billedDate'],
            'paid_date' => $validated['paidDate'] ?? null,
        ]);

        return response()->json($invoice, 201);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function bulkStore(BulkStoreInvoiceRequest $request)
    {
        $bulk = collect($request->all())->map(function ($item) {
            return [
                'customer_id' => $item['customerId'],
                'amount' => $item['amount'],
                'status' => $item['status'],
                'billed_date' => $item['billedDate'],
                'paid_date' => $item['paidDate'] ?? null,
            ];
        });

        Invoice::insert($bulk->toArray());

        return response()->json(['message' => 'Invoices stored successfully'], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        return new InvoiceResource($invoice);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        $invoice->update($request->all());     
        return response()->json(['message' => 'Invoice updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        $user = request()->user();

        if (!$user || !$user->tokenCan('delete')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $invoice->delete();
        return response()->json(['message'=>'Invoice deleted successfully']);
    }
}
