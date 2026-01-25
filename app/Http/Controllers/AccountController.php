<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|max:255']);
        Account::create([...$validated, 'user_id' => Auth::id()]);
        return back();
    }

    public function destroy(Account $account)
    {
        if ($account->user_id !== Auth::id()) abort(403);
        $account->delete();
        return back();
    }
}
