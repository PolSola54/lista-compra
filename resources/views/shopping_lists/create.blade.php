<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Llista de la Compra</title>
</head>
<body>

    <h1>Crear Nova Llista de la Compra</h1>

    <form method="POST" action="{{ route('shopping-lists.store') }}">
        @csrf
        <input type="text" name="name" placeholder="Nom de la llista" required>
        <button type="submit">Crear</button>
    </form>

    <a href="{{ route('shopping-lists.index') }}">Tornar</a>

</body>
</html>
