<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h1>Edit Category</h1>

    <form id="edit-category-form" action="{{ url('/categories/' . $category['id']) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="edit-category-name">Name</label>
            <input type="text" class="form-control" id="edit-category-name" name="name" value="{{ $category['name'] }}" required>
            <div class="invalid-feedback" id="name-error"></div> <!-- Añadir este div para mostrar errores -->
        </div>
        <div class="form-group">
            <label for="edit-category-parent">Parent Category</label>
            <select class="form-control" id="edit-category-parent" name="parent_id">
                <option value="root" @if($category['parent_id'] == 'root') selected @endif>Root</option>
                @foreach($categories as $parent)
                    @if ($parent['id'] !== 'root' && $parent['id'] !== $category['id'])
                        <option value="{{ $parent['id'] }}" @if($parent['id'] == $category['parent_id']) selected @endif>
                            {{ $parent['name'] }}
                        </option>
                    @endif
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update Category</button>
        <a href="{{ url('/categories') }}" class="btn btn-secondary">Back to Categories</a>
    </form>
</div>

<script>
    $(document).ready(function() {
        // Set the CSRF token as a default header for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#edit-category-form').submit(function(event) {
            event.preventDefault();
            var form = $(this);
            $.ajax({
                url: form.attr('action'),
                type: 'PUT',
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        window.location.href = '/categories';
                    } else {
                        alert('Error updating category');
                    }
                },
                error: function(xhr, status, error) {
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        if (errors && errors.name) {
                            $('#edit-category-name').addClass('is-invalid');
                            $('#name-error').text(errors.name[0]);
                        }
                    } else {
                        console.error('Error:', xhr.responseText);
                        alert('Error updating category');
                    }
                }
            });
        });
    });
</script>
</body>
</html>
