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
        try {
            $validator = Validator::make($request->all(), [
                'customer_type' => 'required|in:member,non-member',
                'customer_phone' => 'required_if:customer_type,member',
                'products' => 'required|array',
                'total_pay' => 'required|numeric|min:0',
            ], [
                'customer_phone.required_if' => 'The phone number is required for members.',
                'products.required' => 'Please select at least one product.',
                'total_pay.required' => 'Please enter the payment amount.',
                'total_pay.numeric' => 'The payment amount must be a number.',
                'total_pay.min' => 'The payment amount cannot be negative.',
            ]);

            if ($validator->fails()) {
                return back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Please check your input and try again.');
            }

            // Validate if any products are selected and have valid quantities
            $hasProducts = false;
            $totalPrice = 0;
            $selectedProducts = [];
            $invalidQuantities = [];

            // First pass: collect selected products
            foreach ($request->products as $productId => $productData) {
                if (isset($productData['selected']) && $productData['selected']) {
                    $selectedProducts[] = $productId;
                }
            }

            // Second pass: validate quantities
            foreach ($request->products as $productId => $productData) {
                if (isset($productData['quantity']) && $productData['quantity'] > 0) {
                    if (!in_array($productId, $selectedProducts)) {
                        $product = Product::findOrFail($productId);
                        $invalidQuantities[] = $product->name;
                    } else {
                        $hasProducts = true;
                        $product = Product::findOrFail($productId);
                        $totalPrice += $product->price * $productData['quantity'];

                        // Check stock availability
                        if ($productData['quantity'] > $product->stock) {
                            return back()
                                ->withInput()
                                ->withErrors(['error' => "Insufficient stock for {$product->name}. Available: {$product->stock}"]);
                        }
                    }
                }
            }

            // Check for quantities without product selection
            if (!empty($invalidQuantities)) {
                return back()
                    ->withInput()
                    ->withErrors(['error' => 'Please select these products before setting their quantities: ' . implode(', ', $invalidQuantities)]);
            }

            if (!$hasProducts) {
                return back()
                    ->withInput()
                    ->withErrors(['error' => 'Please select at least one product and specify its quantity.']);
            }

            if ($request->total_pay < $totalPrice) {
                return back()
                    ->withInput()
                    ->withErrors(['error' => "Payment amount (Rp " . number_format($request->total_pay, 0, ',', '.') .
                        ") is less than total price (Rp " . number_format($totalPrice, 0, ',', '.') . ")"]);
            }

            DB::beginTransaction();
            try {

                $points = floor($totalPrice * 0.01);

                $customer = null;
                if ($request->customer_type == "member" && !empty($request->customer_phone)) {
                    $customer = Customer::where('no_telp', $request->customer_phone)->first();

                    if (!$customer) {
                        // Create new customer with only the points from this transaction
                        $customer = Customer::create([
                            'name' => 'New Member',
                            'no_telp' => $request->customer_phone,
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

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'An unexpected error occurred: ' . $e->getMessage()]);
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

        // Check if points were actually used for discount
        if ($transaction->total_pay < $transaction->total_price) {
            // Points were used for discount
            $discount = $transaction->total_price - $transaction->total_pay;
            $finalPrice = $transaction->total_pay;
        }

        $pdf = Pdf::loadView('transactions.receipt', [
            'transaction' => $transaction,
            'discount' => $discount,
            'finalPrice' => $finalPrice,
            'pointsUsed' => $discount > 0
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
            // Calculate discount from points
            $discount = $transaction->customer->poin;
            $finalPrice = $transaction->total_price - $discount;

            // Update total_pay to reflect point usage
            $transaction->update([
                'total_pay' => $finalPrice
            ]);

            // Reset points to only new points earned
            $transaction->customer->update([
                'poin' => $transaction->poin
            ]);
        } else {
            // Add new points to existing points
            $transaction->customer->increment('poin', $transaction->poin);
        }

        if ($request->has('name') && $transaction->customer->status === 'new') {
            $transaction->customer->update([
                'name' => $request->name,
                'status' => 'old'
            ]);
        }

        // Refresh the model to get accurate values
        $transaction->refresh();

        return view('transactions.final', [
            'transaction' => $transaction,
            'discount' => $discount,
            'finalPrice' => $finalPrice
        ]);
    }
}
