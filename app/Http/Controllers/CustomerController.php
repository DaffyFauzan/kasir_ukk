<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $customer->update([
            'name' => $request->name,
        ]);

        return redirect()->route('transactions.finalize', ['transaction' => $customer->id])
            ->with('success', 'Customer name updated successfully.');
    }
}
