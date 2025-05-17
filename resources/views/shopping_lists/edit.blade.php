<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Llista de la Compra</title>
</head>
<body>

    <h1>Editar Llista de la Compra</h1>

    <form method="POST" action="{{ route('shopping-lists.update', $shoppingList->id) }}">
        @csrf
        @method('PUT')
        <input type="text" name="name" value="{{ $shoppingList->name }}" required>
        <button type="submit">Actualitzar</button>
    </form>

    <a href="{{ route('shopping-lists.index') }}">Tornar</a>

</body>
</html>
