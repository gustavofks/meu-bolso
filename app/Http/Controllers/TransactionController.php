<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Salva uma nova transação (Entrada ou Saída).
     */
    public function store(Request $request)
    {
        // 1. Validação rigorosa
        $validated = $request->validate([
            'description'       => 'required|string|max:255',
            'amount'            => 'required|numeric|min:0.01',
            'date'              => 'required|date',
            'type'              => 'required|in:income,expense',
            'category_id'       => 'nullable|exists:categories,id',
            'account_id'        => 'nullable|exists:accounts,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'tags'              => 'nullable|array'
        ]);

        try {
            // 2. Criar a transação (removendo as tags do array principal)
            $data = $validated;
            unset($data['tags']); // Removemos as tags para não dar erro no Transaction::create

            $data['user_id'] = Auth::id(); // Garante o ID do usuário logado

            $transaction = Transaction::create($data);

            // 3. Salvar o relacionamento Many-to-Many (Tabela Pivô)
            if (!empty($validated['tags'])) {
                // O sync() é mais seguro que o attach() pois evita duplicatas
                $transaction->tags()->sync($validated['tags']);
            }

            return back();

        } catch (\Exception $e) {
            // Se houver erro, ele retornará para a tela com a mensagem de erro
            return back()->withErrors(['error' => 'Erro ao salvar: ' . $e->getMessage()]);
        }
    }

    /**
     * Atualiza uma transação existente.
     */
    public function update(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) abort(403);

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'type' => 'required|in:income,expense',
            'category_id' => 'nullable|exists:categories,id',
            'account_id' => 'nullable|exists:categories,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'tags' => 'nullable|array'
        ]);

        $transaction->update(collect($validated)->except('tags')->toArray());

        // Sincroniza as tags (remove as antigas e adiciona as novas)
        if ($request->has('tags')) {
            $transaction->tags()->sync($request->tags);
        }

        return back();
    }

    /**
     * Remove uma transação.
     */
    public function destroy(Transaction $transaction)
    {
        // Segurança: Verifica se a transação pertence ao usuário logado
        if ($transaction->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado.');
        }

        $transaction->delete();

        return back();
    }
}
