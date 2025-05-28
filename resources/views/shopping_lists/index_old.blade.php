<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Llistes de la Compra</title>
</head>
<body>

    <h1>Llistes de la Compra</h1>

    <a href="{{ route('shopping-lists.create') }}">Crear Nova Llista</a>

    <ul>
        @foreach ($shoppingLists as $shoppingList)
            <li>
                <a href="{{ route('shopping-lists.show', $shoppingList->id) }}">{{ $shoppingList->name }}</a>
            </li>
        @endforeach
    </ul>

</body>
</html>
