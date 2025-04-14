<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;

class TransactionHistoryController extends Controller
{
    public function index()
    {
        $transactions = Sale::with(['detailSales.product', 'customer', 'staff'])
            ->orderBy('sale_date', 'desc')
            ->get();

        return view('transactions.index', compact('transactions'));
    }

    public function create()
    {
        $products = Product::all();
        $customers = Customer::all();
        return view('transactions.create', compact('products', 'customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'products' => 'required|array',
            'products.*.selected' => 'nullable|boolean',
            'products.*.quantity' => 'nullable|integer|min:1',
            'total_pay' => 'required|integer|min:0',
        ]);

        $total_price = 0;
        $details = [];

        foreach ($validated['products'] as $productId => $productData) {
            if (isset($productData['selected']) && $productData['selected']) {
                $product = Product::findOrFail($productId);
                $quantity = $productData['quantity'];
                $sub_total = $product->price * $quantity;

                $total_price += $sub_total;

                $details[] = [
                    'product_id' => $productId,
                    'amount' => $quantity,
                    'sub_total' => $sub_total,
                ];
            }
        }

        if ($validated['total_pay'] < $total_price) {
            return redirect()->back()->withErrors(['total_pay' => 'Total payment is less than the total price of the selected products.']);
        }

        $sale = Sale::create([
            'sale_date' => now(),
            'total_price' => $total_price,
            'total_pay' => $validated['total_pay'],
            'total_return' => $validated['total_pay'] - $total_price,
            'customer_id' => $validated['customer_id'],
            'staff_id' => auth()->id(),
        ]);

        foreach ($details as $detail) {
            $sale->detailSales()->create($detail);

            $product = Product::findOrFail($detail['product_id']);
            $product->decrement('stock', $detail['amount']);
        }

        return redirect()->route('transactions.index')->with('success', 'Transaction created successfully.');
    }
}
