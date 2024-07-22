<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Category</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h1>Search Category</h1>

    <form id="search-category-form" class="mb-3">
        <div class="form-group">
            <label for="search-category-id">Category ID</label>
            <input type="text" class="form-control" id="search-category-id" name="search" required>
        </div>
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <div id="category-details" style="display: none;">
        <h2>Category Details</h2>
        <ul class="list-group">
            <li class="list-group-item"><strong>ID:</strong> <span id="category-id"></span></li>
            <li class="list-group-item"><strong>Name:</strong> <span id="category-name"></span></li>
            <li class="list-group-item"><strong>Parent ID:</strong> <span id="category-parent-id"></span></li>
        </ul>
    </div>

    <a href="{{ url('/categories') }}" class="btn btn-secondary mt-3">Back to Categories</a>
</div>

<script>
    $(document).ready(function() {
        $('#search-category-form').submit(function(event) {
            event.preventDefault();
            var searchValue = $('#search-category-id').val();

            $.get('/categories/' + searchValue, function(data) {
                console.log("Category data received:", data); // Mensaje de depuración
                $('#category-id').text(data.id);
                $('#category-name').text(data.name);
                $('#category-parent-id').text(data.parent_id);
                $('#category-details').show();
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error("Error searching category:", textStatus, errorThrown); // Mensaje de error
                alert('Category not found');
                $('#category-details').hide();
            });
        });
    });
</script>
</body>
</html>
