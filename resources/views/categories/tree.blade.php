<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Tree</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
            border: none;
        }
        ul {
            list-style-type: none;
            padding-left: 20px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1>Category Tree</h1>

    <div class="mb-3">
        <a href="{{ url('/categories') }}" class="btn btn-secondary">Back to Categories</a>
        <button class="btn btn-primary" onclick="history.back()">Volver Atrás</button>
    </div>

    <h2>Categories</h2>
    <ul id="category-tree" class="list-group"></ul>
</div>

<script>
    $(document).ready(function() {
        // Set the CSRF token as a default header for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.getJSON('{{ url("/categories/tree") }}', function(data) {
            console.log(data); // Imprime el árbol en la consola para verificar
            buildTree($('#category-tree'), data);
        });

        function buildTree(parent, categories) {
            $.each(categories, function(index, category) {
                var li = $('<li class="list-group-item"></li>');
                var span = $('<span class="caret"></span>').text(category.name);
                li.append(span);

                if (category.children && category.children.length > 0) {
                    var ul = $('<ul class="nested list-group"></ul>');
                    buildTree(ul, category.children);
                    li.append(ul);
                }

                parent.append(li);
            });

            parent.find('.caret').click(function() {
                $(this).siblings('.nested').toggle();
                $(this).toggleClass('caret-down');
            });
        }
    });
</script>
<meta name="csrf-token" content="{{ csrf_token() }}">
</body>
</html>
