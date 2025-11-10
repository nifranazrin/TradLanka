@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h3>Add New Category</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control"></textarea>
        </div>

        <div class="mb-3">
            <label>Image</label>
            <input type="file" name="image" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label d-block">Status</label>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="status" id="statusActive" value="1" checked>
                <label class="form-check-label" for="statusActive">Active</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="status" id="statusInactive" value="0">
                <label class="form-check-label" for="statusInactive">Inactive</label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save Category</button>
    </form>
</div>
@endsection
