<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
<div class="container mt-5">
    <h1>Add Category</h1>

    <form action="{{ url('/api/categories') }}" method="POST" id="add-category-form">
        @csrf
        <div class="form-group">
            <label for="category-name">Name</label>
            <input type="text" class="form-control" id="category-name" name="name" required>
            <div class="invalid-feedback" id="name-error"></div> <!-- Añadir este div para mostrar errores -->
        </div>
        <div class="form-group">
            <label for="parent-category">Parent Category</label>
            <select class="form-control" id="parent-category" name="parent_id">
                <option value="root">Root</option>
                @foreach ($categories as $category)
                    @if ($category['id'] !== 'root')
                        <option value="{{ $category['id'] }}">{{ $prefix }}{{ $category['name'] }}</option>
                        @if (!empty($category['children']))
                            @foreach ($category['children'] as $child)
                                <option value="{{ $child['id'] }}">{{ $prefix . '-- ' }}{{ $child['name'] }}</option>
                            @endforeach
                        @endif
                    @endif
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Add Category</button>
        <a href="{{ url('/categories') }}" class="btn btn-secondary">Back to Categories</a>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
    $(document).ready(function() {
        // Set the CSRF token as a default header for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#add-category-form').submit(function(event) {
            event.preventDefault(); // Prevent the form from submitting via the browser
            $.ajax({
                url: $(this).attr('action'),
                method: $(this).attr('method'),
                data: $(this).serialize(),
                success: function(response) {
                    alert('Category added successfully');
                    window.location.href = '/categories';
                },
                error: function(xhr, status, error) {
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        if (errors && errors.name) {
                            $('#category-name').addClass('is-invalid');
                            $('#name-error').text(errors.name[0]);
                        }
                    } else {
                        alert('Error adding category');
                        console.error('Error:', xhr.responseText);
                    }
                }
            });
        });
    });
</script>
</body>
</html>