<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\GoalTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoalController extends Controller
{
    public function index()
    {
        return inertia('Metas', [
            'dbMetas' => Goal::where('user_id', Auth::id())->with('account')->get(),
            'dbContas' => \App\Models\Account::where('user_id', Auth::id())->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'target_amount' => 'required|numeric',
            'account_id' => 'required|exists:accounts,id'
        ]);

        Goal::create([...$validated, 'user_id' => Auth::id()]);
        return back();
    }

    public function update(Request $request, Goal $goal)
    {
        if ($goal->user_id !== Auth::id()) abort(403);
        $request->validate(['amount' => 'required|numeric|min:0.01']);

        // Atualiza o saldo atual da meta
        $goal->increment('current_amount', $request->amount);

        // Registra o histórico
        GoalTransaction::create([
            'goal_id' => $goal->id,
            'amount' => $request->amount,
            'balance_after' => $goal->current_amount,
            'date' => now()
        ]);

        if ($goal->current_amount >= $goal->target_amount) {
            $goal->update(['status' => 'concluida']);
        }

        return back();
    }

    public function destroy(Goal $goal)
    {
        if ($goal->user_id !== Auth::id()) abort(403);
        $goal->delete();
        return back();
    }
}
