<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Llista de la Compra: {{ $shoppingList->name }}</title>
</head>
<body>

    <h1>{{ $shoppingList->name }}</h1>

    <h2>Categorías</h2>

    <ul>
        @foreach ($categories as $category)
            <li>
                <strong>{{ $category->name }}</strong>
                <ul>
                    @foreach ($category->items as $item)
                        <li>
                            {{ $item->name }}
                            @if($item->is_completed)
                                <span>(Completat)</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </li>
        @endforeach
    </ul>

    <a href="{{ route('shopping-lists.edit', $shoppingList->id) }}">Editar Llista</a>
    <form method="POST" action="{{ route('shopping-lists.destroy', $shoppingList->id) }}" style="display: inline;">
        @csrf
        @method('DELETE')
        <button type="submit">Esborrar Llista</button>
    </form>

    <a href="{{ route('shopping-lists.index') }}">Tornar</a>

</body>
</html>
