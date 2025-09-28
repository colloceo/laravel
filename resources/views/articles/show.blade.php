@extends('layouts.public')

@section('title', $article->title . ' - News254')
@section('description', $article->excerpt)

@section('og-meta')
    <meta property="og:title" content="{{ $article->title }}" />
    <meta property="og:type" content="article" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:description" content="{{ $article->excerpt }}" />
    @if($article->image)
        <meta property="og:image" content="{{ Str::startsWith($article->image, 'http') ? $article->image : asset('uploads/' . $article->image) }}" />
    @endif
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    {{-- MAIN ARTICLE CONTENT (LEFT) --}}
    <div class="lg:col-span-2 bg-white rounded-lg shadow-sm p-6 lg:p-8">
        <!-- Breadcrumbs -->
        <nav class="text-sm text-gray-500 mb-4">
            <a href="{{ route('home') }}" class="hover:text-gray-900">Home</a> &raquo;
            <a href="{{ route('categories.articles', $article->category) }}" class="hover:text-gray-900">{{ $article->category->name }}</a>
        </nav>

        <h1 class="text-3xl lg:text-4xl font-extrabold text-gray-900 mb-3 leading-tight">{{ $article->title }}</h1>
        
        <!-- Meta Info -->
        <div class="flex items-center text-sm text-gray-500 mb-6">
            <span>By {{ $article->user->name }}</span>
            <span class="mx-2">&bull;</span>
            <span>{{ $article->published_at->format('F j, Y') }}</span>
        </div>

        @if($article->image)
            <img src="{{ Str::startsWith($article->image, 'http') ? $article->image : asset('uploads/' . $article->image) }}" alt="{{ $article->title }}" class="w-full h-auto rounded-lg mb-8">
        @endif

        {{-- Use the 'prose' class for beautiful typography --}}
        <div class="prose max-w-none text-lg">
            {!! $article->content !!}
        </div>
        
        <!-- Comments Section -->
        <section class="mt-12 pt-8 border-t" id="comments">
             <h3 class="text-2xl font-bold mb-6">Comments ({{ $article->comments->count() }})</h3>
             <div class="bg-gray-100 rounded-lg p-6">
                <form action="{{ route('comments.store', $article) }}" method="POST">
                    @csrf
                     <textarea name="content" rows="4" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" placeholder="Your comment..." required></textarea>
                     <input type="text" name="author_name" class="w-full border-gray-300 rounded-md mt-4 focus:ring-blue-500 focus:border-blue-500" placeholder="Your Name" required>
                     <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded font-semibold mt-4 hover:bg-gray-700">Post Comment</button>
                </form>
             </div>
             <div class="space-y-8 mt-8">
                 @forelse($article->comments as $comment)
                     <div class="flex items-start space-x-4">
                         <img src="https://ui-avatars.com/api/?name={{ urlencode($comment->author_name) }}&background=f3f4f6&color=111827" class="w-12 h-12 rounded-full" alt="avatar">
                         <div>
                             <p class="font-bold text-gray-900">{{ $comment->author_name }}</p>
                             <p class="text-xs text-gray-500 mb-2">{{ $comment->created_at->diffForHumans() }}</p>
                             <p class="text-gray-700 whitespace-pre-wrap">{{ $comment->content }}</p>
                         </div>
                     </div>
                 @empty
                    <p class="text-center text-gray-500 py-4">No comments yet.</p>
                 @endforelse
             </div>
        </section>
    </div>

    {{-- SIDEBAR (RIGHT) --}}
    <aside class="space-y-8">
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <h3 class="text-lg font-bold pb-2 mb-4 border-b">Popular Posts</h3>
            <div class="space-y-4">
                @foreach($sidebarPopular as $popularArticle)
                <div class="flex items-start">
                    <span class="text-3xl font-bold text-gray-200 mr-3">{{ $loop->iteration }}</span>
                    <div>
                        <a href="{{ route('articles.show', $popularArticle) }}" class="font-semibold hover:text-gray-600">{{ $popularArticle->title }}</a>
                        <p class="text-xs text-gray-500 mt-1">{{ $popularArticle->published_at->format('M j, Y') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </aside>
</div>
@endsection