@extends('layouts.public')

{{-- Fill in the SEO placeholders --}}
@section('title')
    @if(isset($query)) Search Results for "{{ $query }}"
    @elseif(isset($category)) Articles in: {{ $category->name }}
    @elseif(isset($tag)) Articles tagged with: {{ $tag->name }}
    @endif
    - News254
@endsection
@section('description', 'Browse the latest articles from News254, Kenya\'s News Hub.')

@section('content')
    <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
        @if(isset($query))
            <h1 class="text-3xl font-extrabold text-gray-900">Search Results for "{{ $query }}"</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $articles->total() }} articles found.</p>
        @elseif(isset($category))
            <h1 class="text-3xl font-extrabold text-gray-900">Category: {{ $category->name }}</h1>
        @elseif(isset($tag))
            <h1 class="text-3xl font-extrabold text-gray-900">Tag: {{ $tag->name }}</h1>
        @endif
    </div>
    <div class="space-y-8">
        @forelse($articles as $article)
            <div class="bg-white rounded-lg shadow-sm grid grid-cols-1 md:grid-cols-2 gap-6 items-center p-4">
                <a href="{{ route('articles.show', $article) }}">
                    <img src="{{ Str::startsWith($article->image, 'http') ? $article->image : asset('uploads/' . $article->image) }}" class="rounded-md w-full">
                </a>
                <div>
                    <a href="{{ route('categories.articles', $article->category) }}" class="text-blue-600 font-semibold text-sm">{{ $article->category->name }}</a>
                    <h2 class="text-xl font-bold mt-1"><a href="{{ route('articles.show', $article) }}" class="hover:text-gray-600">{{ $article->title }}</a></h2>
                    <p class="text-sm text-gray-500 mt-2">{{ $article->excerpt }}</p>
                    <div class="text-xs text-gray-500 mt-3">
                        <span>By {{ $article->user->name }}</span>
                        <span class="mx-1">&bull;</span>
                        <span>{{ $article->published_at->format('M j, Y') }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                <p class="text-lg">No articles found for this selection.</p>
            </div>
        @endforelse
    </div>
    <div class="mt-8">
        {{ $articles->appends(request()->query())->links() }}
    </div>
@endsection