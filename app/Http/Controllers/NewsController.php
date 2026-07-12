<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::latestPublished()->paginate(9);

        return view('public.news.index', compact('news'));
    }

    public function show(Request $request, string $slug)
    {
        $newsItem = News::published()->where('slug', $slug)->firstOrFail();

        $sessionKey = 'news_viewed_' . $newsItem->id;
        if (!$request->session()->has($sessionKey)) {
            $newsItem->incrementViews();
            $request->session()->put($sessionKey, true);
            $newsItem->refresh();
        }

        $related = News::latestPublished()
            ->where('id', '!=', $newsItem->id)
            ->limit(3)
            ->get();

        $shareUrls = $newsItem->shareUrls();

        return view('public.news.show', compact('newsItem', 'related', 'shareUrls'));
    }
}
