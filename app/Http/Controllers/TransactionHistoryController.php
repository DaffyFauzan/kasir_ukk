<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransactionsExport;
use Barryvdh\DomPDF\Facade\Pdf;

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
            'customer_type' => 'required|in:member,non-member',
            'customer_phone' => 'nullable|required_if:customer_type,member',
            'products' => 'required|array',
            'products.*.quantity' => 'required|integer|min:1',
            'total_pay' => 'required|numeric|min:0',
        ]);

        $totalPrice = 0;
        foreach ($request->products as $productId => $productData) {
            $product = Product::findOrFail($productId);
            $totalPrice += $product->price * $productData['quantity'];
        }

        if ($request->total_pay < $totalPrice) {
            return back()->withErrors(['total_pay' => 'Total payment is less than the total price.']);
        }

        $customer = null;
        $pointsUsed = 0;
        $discount = 0;

        if ($request->customer_type === 'member' && $request->customer_phone) {
            $customer = Customer::firstOrCreate(
                ['no_telp' => $request->customer_phone],
                ['name' => 'New Member']
            );

            if ($customer->wasRecentlyCreated) {
                $pointsUsed = 0;
            } else {
                $pointsUsed = min($customer->poin, $totalPrice);
                $discount = $pointsUsed;
            }

            $customer->poin = $customer->poin - $pointsUsed + floor(($totalPrice - $discount) * 0.01);
            $customer->save();
        }

        $sale = Sale::create([
            'sale_date' => now(),
            'total_price' => $totalPrice,
            'total_pay' => $request->total_pay,
            'total_return' => $request->total_pay - ($totalPrice - $discount),
            'poin' => $pointsUsed,
            'total_poin' => $customer ? $customer->poin : 0,
            'customer_id' => $customer ? $customer->id : null,
            'staff_id' => auth()->id(),
            'customer_type' => $request->customer_type, // Save customer_type
            'customer_phone' => $request->customer_phone, // Save customer_phone
        ]);

        foreach ($request->products as $productId => $productData) {
            $product = Product::findOrFail($productId);
            $sale->detailSales()->create([
                'product_id' => $productId,
                'amount' => $productData['quantity'],
                'sub_total' => $product->price * $productData['quantity'],
            ]);

            $product->decrement('stock', $productData['quantity']);
        }

        if ($request->customer_type === 'member') {
            return redirect()->route('transactions.points', $sale->id);
        }

        return redirect()->route('transactions.index')->with('success', 'Transaction saved successfully.');
    }

    public function export()
    {
        return Excel::download(new TransactionsExport, 'transactions.xlsx');
    }

    public function exportReceipt(Sale $transaction)
    {
        $transaction->load(['detailSales.product', 'customer', 'staff']);

        $pdf = Pdf::loadView('transactions.receipt', compact('transaction'));
        return $pdf->download('receipt-' . $transaction->id . '.pdf');
    }

    public function points(Sale $transaction)
    {
        $transaction->load('customer');
        return view('transactions.points', compact('transaction'));
    }

    public function finalize(Request $request, Sale $transaction)
    {
        $usePoints = $request->has('use_points');
        $discount = $usePoints ? $transaction->poin : 0;

        return view('transactions.final', [
            'transaction' => $transaction,
            'discount' => $discount,
            'finalPrice' => $transaction->total_price - $discount,
        ]);
    }
}
