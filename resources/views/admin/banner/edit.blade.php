@extends('layouts.admin')

@section('content')

@php
    use Illuminate\Support\Str;
@endphp

<div class="min-h-screen bg-[#f5f4f2] py-10">

    <div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">

        {{-- HEADER --}}
        <div class="px-8 py-6 border-b bg-white">
            <h2 class="text-2xl font-semibold text-gray-800">Manage Home Banner</h2>
        </div>

        {{-- ERRORS --}}
        @if($errors->any())
            <div class="mx-8 mt-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.banner.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="px-8 py-8 grid grid-cols-1 md:grid-cols-3 gap-8">

                {{-- LEFT : IMAGE --}}
                <div class="md:col-span-1 bg-gray-50 p-6 rounded-xl border border-gray-100 flex flex-col items-center text-center">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">
                        Current Banner
                    </p>

                    @php
                        $imgSrc = null;
                        if (!empty($banner->image_path)) {
                            $imgSrc = Str::startsWith($banner->image_path, 'http')
                                ? $banner->image_path
                                : asset('storage/'.$banner->image_path);
                        }
                    @endphp

                    {{-- IMAGE --}}
                    <div class="w-full h-32 rounded-lg overflow-hidden border border-gray-200 shadow-sm cursor-pointer"
                         @if($imgSrc) onclick="previewImage()" @endif>

                        @if($imgSrc)
                            <img src="{{ $imgSrc }}"
                                 alt="Current Banner"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gray-200 flex items-center justify-center text-xs text-gray-500">
                                No banner image
                            </div>
                        @endif
                    </div>

                    {{-- PREVIEW BUTTON --}}
                    @if($imgSrc)
                        <button type="button"
                                onclick="previewImage()"
                                class="mt-3 text-sm text-[#5b2c2c] hover:text-[#4a2424] font-semibold flex items-center gap-2">
                            <i class="bi bi-eye-fill"></i> Preview Full Size
                        </button>
                        <input type="hidden" id="fullImageUrl" value="{{ $imgSrc }}">
                    @endif

                    <hr class="w-full border-gray-200 my-5">

                    {{-- UPLOAD --}}
                    <div class="w-full text-left">
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2 block">
                            Upload New
                        </label>
                        <input type="file" name="image" accept="image/*"
                               class="block w-full text-xs text-slate-500
                               file:mr-4 file:py-2 file:px-4
                               file:rounded-full file:border-0
                               file:text-xs file:font-semibold
                               file:bg-[#5b2c2c] file:text-white
                               hover:file:bg-[#4a2424]
                               cursor-pointer border border-gray-300 rounded-lg bg-white p-1">
                        <p class="text-[10px] text-gray-400 mt-2">
                            Recommended: 1200×400px (Max 2MB)
                        </p>
                    </div>
                </div>

                {{-- RIGHT : CONTENT --}}
                <div class="md:col-span-2">
                    <h3 class="text-lg font-bold text-gray-800 mb-6 border-b pb-2">
                        Banner Details
                    </h3>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Headline Title</label>
                            <input type="text" name="title"
                                   value="{{ old('title', $banner->title) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#5b2c2c] focus:border-transparent">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Button Label</label>
                                <input type="text" name="button_text"
                                       value="{{ old('button_text', $banner->button_text) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#5b2c2c] focus:border-transparent">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Button Link (URL)</label>
                                <input type="text" name="button_link"
                                       value="{{ old('button_link', $banner->button_link) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#5b2c2c] focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <div class="mt-10 flex justify-end">
                        <button type="submit"
                                class="px-8 py-3 bg-[#5b2c2c] text-white rounded-lg shadow-md hover:bg-[#4a2424] transition flex items-center">
                            <i class="bi bi-save2-fill mr-2"></i> Save Changes
                        </button>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- SWEETALERT --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function previewImage() {
    const url = document.getElementById('fullImageUrl')?.value;
    if (!url) return;

    Swal.fire({
        imageUrl: url,
        imageAlt: 'Current Banner',
        width: '80%',
        showConfirmButton: false,
        showCloseButton: true,
        backdrop: 'rgba(0,0,0,0.8)'
    });
}
</script>

@endsection
