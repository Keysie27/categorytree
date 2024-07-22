<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <style>
        .nested {
            display: none;
            margin-left: 20px;
        }
        .caret {
            cursor: pointer;
            user-select: none;
        }
        .caret::before {
            content: "\25B6";
            color: black;
            display: inline-block;
            margin-right: 6px;
        }
        .caret-down::before {
            transform: rotate(90deg);
        }
        .list-group-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .category-details {
            flex-grow: 1;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1>Category Management</h1>

    <div class="mb-3">
        <a href="{{ url('/categories/create') }}" class="btn btn-primary">Add Category</a>
        <a href="{{ url('/categories/tree') }}" class="btn btn-secondary">View Category Tree</a>
        <a href="{{ url('/categories/search') }}" class="btn btn-secondary">Search Category</a>
    </div>

    <h2>Categories</h2>
    <ul id="category-list" class="list-group">
        @foreach ($categories as $category)
            @if ($category['id'] !== 'root')
                <li class="list-group-item">
                    <div class="category-details">
                        <strong>Name:</strong> {{ $category['name'] }}<br>
                        <strong>ID:</strong> {{ $category['id'] }}<br>
                        <strong>Parent ID:</strong> {{ $category['parent_id'] ?? 'None' }}
                    </div>
                    <div>
                        <a href="{{ route('categories.edit', ['id' => $category['id']]) }}" class="btn btn-sm btn-warning">Edit</a>
                        <button class="btn btn-sm btn-danger delete-category" data-id="{{ $category['id'] }}">Delete</button>
                    </div>
                </li>
            @endif
        @endforeach
    </ul>
</div>

<script>
    $(document).ready(function() {
        // Set the CSRF token as a default header for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Delete category
        $('.delete-category').click(function() {
            var categoryId = $(this).data('id');
            var listItem = $(this).closest('li');

            if (confirm('Are you sure you want to delete this category? This will delete all its subcategories as well.')) {
                $.ajax({
                    url: '/categories/' + categoryId,
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            alert('Category and its subcategories have been successfully deleted.');
                            location.reload();
                        } else {
                            alert('Error deleting category');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', xhr.responseText);
                        alert('Error deleting category');
                    }
                });
            }
        });
    });
</script>
</body>
</html>
