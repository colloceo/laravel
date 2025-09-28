{{-- resources/views/admin/articles/_form.blade.php --}}

@if ($errors->any())
    <div class="mb-4">
        <ul class="list-disc list-inside text-sm text-red-600">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    {{-- Form Section --}}
    <div>
        <!-- Title -->
        <div class="mb-4">
            <x-input-label for="title" :value="__('Title')" />
            {{-- We need to pass the dark mode classes to the component --}}
            <x-text-input id="title" class="block mt-1 w-full dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300" type="text" name="title" :value="old('title', $article->title ?? '')" required />
        </div>
        
        <!-- Category -->
        <div class="mb-4">
            <x-input-label for="category_id" :value="__('Category')" />
            {{-- Add dark mode classes to the select element --}}
            <select name="category_id" id="category_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $article->category_id ?? '') == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Image Upload -->
        <div class="mb-4">
            <x-input-label for="image" :value="__('Featured Image Upload')" />
            @if(isset($article) && $article->image)
                <div class="my-2">
                    <img src="{{ Str::startsWith($article->image, 'http') ? $article->image : asset('uploads/' . $article->image) }}" alt="Current Image" class="w-32 h-auto rounded">
                </div>
                <input type="hidden" name="current_image" value="{{ $article->image }}">
            @endif
            {{-- Add dark mode classes to the file input --}}
            <input id="image" class="block mt-1 w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" type="file" name="image">
        </div>

        <!-- Image URL -->
        <div class="mb-4">
            <x-input-label for="image_url" :value="__('Or Image URL')" />
            <x-text-input id="image_url" class="block mt-1 w-full dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300" type="text" name="image_url" :value="old('image_url', (isset($article) && Str::startsWith($article->image, 'http')) ? $article->image : '')" placeholder="https://example.com/image.jpg" />
        </div>
        
        <!-- Tags -->
        <div class="mb-4">
            <x-input-label for="tags" :value="__('Tags (comma-separated)')" />
            <x-text-input id="tags" class="block mt-1 w-full dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300" type="text" name="tags" :value="old('tags', isset($article) ? $article->tags->pluck('name')->implode(', ') : '')" />
        </div>
        
        <!-- Excerpt -->
        <div class="mb-4">
            <x-input-label for="excerpt" :value="__('Excerpt')" />
            {{-- Add dark mode classes to the textarea --}}
            <textarea name="excerpt" id="excerpt" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">{{ old('excerpt', $article->excerpt ?? '') }}</textarea>
        </div>
        
        <!-- Content (Trix Editor) -->
        <div class="mb-4">
            <x-input-label for="content" :value="__('Content')" />
            <input id="content" type="hidden" name="content" value="{{ old('content', $article->content ?? '') }}">
            {{-- Add dark mode classes to the Trix editor container --}}
            <trix-editor input="content" class="bg-white block mt-1 w-full border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-900"></trix-editor>
        </div>
        
        <!-- Status -->
        <div class="mb-4">
            <x-input-label for="status" :value="__('Status')" />
            <select name="status" id="status" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                <option value="draft" @selected(old('status', $article->status ?? '') == 'draft')>Draft</option>
                <option value="published" @selected(old('status', $article->status ?? '') == 'published')>Published</option>
                <option value="featured" @selected(old('status', $article->status ?? '') == 'featured')>Featured</option>
                <option value="breaking" @selected(old('status', $article->status ?? '') == 'breaking')>Breaking</option>
            </select>
        </div>
    </div>

    {{-- Live Preview Section --}}
    <div class="bg-gray-50 p-4 rounded-lg border">
        <h3 class="text-lg font-bold mb-4">Live Preview</h3>
        <div id="preview-image-container" class="mb-4 hidden">
                <img id="preview-image" src="" alt="Image Preview" class="w-full h-auto rounded-lg">
        </div>
        <h1 id="preview-title" class="text-3xl font-bold mb-2">Article Title</h1>
        <p class="text-gray-500 mb-4">Category: <span id="preview-category">None</span></p>
        <div id="preview-content" class="prose max-w-none">
            <p>Start typing in the content section to see a preview...</p>
        </div>
    </div>
</div>

<div class="flex items-center justify-end mt-4 space-x-4">
    @if(isset($article))
        <a href="{{ route('admin.articles.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400">
            Cancel
        </a>
    @endif
    <x-primary-button>
        {{ isset($article) ? __('Update Article') : __('Save Article') }}
    </x-primary-button>
</div>

{{-- Trix and Live Preview Scripts --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- Live Preview for Trix Editor ---
        const trixEditor = document.querySelector("trix-editor");
        trixEditor.addEventListener("trix-change", function(event) {
            const content = event.target.value; 
            document.getElementById('preview-content').innerHTML = content;
        });

        // --- Live Preview for other fields ---
        const titleInput = document.getElementById('title');
        const categorySelect = document.getElementById('category_id');
        const imageInput = document.getElementById('image');
        const imageUrlInput = document.getElementById('image_url');
        const previewTitle = document.getElementById('preview-title');
        const previewCategory = document.getElementById('preview-category');
        const previewImageContainer = document.getElementById('preview-image-container');
        const previewImage = document.getElementById('preview-image');

        titleInput.addEventListener('keyup', () => {
            previewTitle.textContent = titleInput.value || 'Article Title';
        });

        categorySelect.addEventListener('change', () => {
            previewCategory.textContent = categorySelect.options[categorySelect.selectedIndex].text || 'None';
        });
        
        function updateImagePreview(src) {
            if (src) {
                previewImage.src = src;
                previewImageContainer.classList.remove('hidden');
            } else {
                previewImageContainer.classList.add('hidden');
            }
        }

        imageInput.addEventListener('change', (event) => {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) { updateImagePreview(e.target.result); }
                reader.readAsDataURL(file);
                imageUrlInput.value = null;
            }
        });

        imageUrlInput.addEventListener('input', () => {
            updateImagePreview(imageUrlInput.value);
            imageInput.value = null; 
        });
    });
</script>
@endpush