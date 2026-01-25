<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TagController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|max:255']);

        Tag::create([...$validated, 'user_id' => Auth::id()]);

        return back();
    }

    public function destroy(Tag $tag)
    {
        if ($tag->user_id !== Auth::id()) abort(403);
        $tag->delete();
        return back();
    }
}
