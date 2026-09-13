<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function create()
    {
        $news = News::all();
        return view('admin.create'));
    }

    
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'image' => 'nullable|image',
            'category' => 'required|string'
        ]);

        News::create($data);
        return redirect()->route('admin.noticias')->with('success', 'Noticia creada exitosamente.');
    }

    public function edit($id)
    {
        $news = News::findOrFail($id);
        return view('admin.news.edit', compact('news'));
    }

    public function update(Request $request, $id)
    {
        $news = News::findOrFail($id);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'image' => 'nullable|image',
            'category' => 'required|string'
        ]);

        $news->update($data);
        return redirect()->route('admin.noticias')->with('success', 'Noticia actualizada exitosamente.');
    }

    public function destroy($id)
    {
        $news = News::findOrFail($id);
        $news->delete();
        return redirect()->route('admin.noticias')->with('success', 'Noticia eliminada exitosamente.');
    }
}
