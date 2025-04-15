<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransactionsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransactionHistoryController extends Controller
{
    public function index()
    {
        $transactions = Sale::with(['detailSales.product', 'customer', 'staff'])
            ->orderBy('id', 'desc')
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
        $totalPrice = 0;
        $validator = Validator::make($request->all(), [
            'customer_type' => 'required|in:member,non-member',
            'customer_phone' => 'nullable',
            'products' => 'required|array',
            'products.*.quantity' => 'nullable|integer|min:1',
            'total_pay' => 'required|numeric|min:0',
        ]);

        $validated = $validator->validated();

        DB::beginTransaction();
        try {

            foreach ($request->products as $productId => $productData) {
                if (isset($productData['quantity']) && $productData['quantity'] > 0) {
                    $product = Product::findOrFail($productId);
                    $totalPrice += $product->price * $productData['quantity'];
                }
            }

            if ($request->total_pay < $totalPrice) {
                return back()->withErrors(['total_pay' => 'Total payment is less than the total price.']);
            }

            $points = floor($totalPrice * 0.01);

            $customer = null;
            if ($validated['customer_type'] == "member" && !empty($validated['customer_phone'])) {
                $customer = Customer::where('no_telp', $validated['customer_phone'])->first();

                if (!$customer) {
                    // Create new customer with only the points from this transaction
                    $customer = Customer::create([
                        'name' => 'New Member',
                        'no_telp' => $validated['customer_phone'],
                        'poin' => 0, // Initialize with 0 points
                        'status' => 'new'
                    ]);
                }
            }

            $sale = Sale::create([
                'sale_date' => now(),
                'total_price' => $totalPrice,
                'total_pay' => $request->total_pay,
                'total_return' => $request->total_pay - $totalPrice,
                'poin' => $points,
                'total_poin' => $customer ? $customer->poin : 0,
                'customer_id' => $customer ? $customer->id : null,
                'staff_id' => auth()->id(),
            ]);

            foreach ($request->products as $productId => $productData) {
                if (isset($productData['quantity']) && $productData['quantity'] > 0) {
                    $product = Product::findOrFail($productId);
                    $sale->detailSales()->create([
                        'product_id' => $productId,
                        'amount' => $productData['quantity'],
                        'sub_total' => $product->price * $productData['quantity'],
                    ]);

                    $product->decrement('stock', $productData['quantity']);
                }
            }

            DB::commit();

            if ($customer) {
                return redirect()->route('transactions.points', ['transaction' => $sale->id])
                    ->with('success', $customer->status === 'new' ? 'New member registered successfully!' : 'Points added to existing member!');
            }

            return redirect()->route('transactions.index')
                ->with('success', 'Transaction saved successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function export()
    {
        return Excel::download(new TransactionsExport, 'transactions.xlsx');
    }

    public function exportReceipt(Sale $transaction)
    {
        $transaction->load('detailSales.product', 'customer', 'staff');

        $discount = 0;
        $finalPrice = $transaction->total_price;

        if ($transaction->customer && $transaction->customer->status === 'old') {
            $discount = $transaction->poin;
            $finalPrice = $transaction->total_price - $discount;
        }

        $pdf = Pdf::loadView('transactions.receipt', [
            'transaction' => $transaction,
            'discount' => $discount,
            'finalPrice' => $finalPrice
        ]);

        return $pdf->download('receipt-' . $transaction->id . '.pdf');
    }

    public function points($transactions)
    {
        $transaction = Sale::with(['customer', 'detailSales.product'])->findOrFail($transactions);

        // Calculate previous points correctly
        $previousPoints = 0;
        if ($transaction->customer && $transaction->customer->status === 'old') {
            // Get the current available points from customer
            $previousPoints = $transaction->customer->poin;
        }

        return view('transactions.points', [
            'transaction' => $transaction,
            'previousPoints' => $previousPoints
        ]);
    }

    public function finalize(Request $request, $transaction)
    {
        $transaction = Sale::with(['customer', 'detailSales.product'])->findOrFail($transaction);

        $discount = 0;
        $finalPrice = $transaction->total_price;

        if ($request->has('use_points') && $transaction->customer && $transaction->customer->status === 'old') {
            // Use current available points as discount
            $discount = $transaction->customer->poin;

            // Reset points to only new points earned from this transaction
            $transaction->customer->update([
                'poin' => $transaction->poin
            ]);
        } else {
            // Add new points to existing points when not using points for discount
            $transaction->customer->update([
                'poin' => $transaction->customer->poin + $transaction->poin
            ]);
        }

        if ($request->has('name') && $transaction->customer->status === 'new') {
            $transaction->customer->update([
                'name' => $request->name,
                'status' => 'old'
            ]);
        }

        $finalPrice = max(0, $transaction->total_price - $discount);

        // Refresh the model to get accurate point values
        $transaction->refresh();

        return view('transactions.final', [
            'transaction' => $transaction,
            'discount' => $discount,
            'finalPrice' => $finalPrice
        ]);
    }
}
