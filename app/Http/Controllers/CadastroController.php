<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\Tag;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CadastroController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return Inertia::render('Cadastros', [
            'dbCategories' => Category::where('user_id', $user->id)->get(),
            'dbAccounts' => Account::where('user_id', $user->id)->get(),
            'dbPaymentMethods' => PaymentMethod::where('user_id', $user->id)->get(),
            'dbTags' => Tag::where('user_id', $user->id)->get(),
        ]);
    }
}
