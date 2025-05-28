<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablir Contrasenya</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4 max-w-md">
        <h1 class="text-2xl font-bold mb-4 text-center">Restablir Contrasenya</h1>
        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">
            <div class="mb-4">
                <label for="email" class="block text-gray-700">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus class="w-full p-2 border rounded @error('email') border-red-500 @enderror">
                @error('email')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-700">Nova Contrasenya</label>
                <input id="password" type="password" name="password" required class="w-full p-2 border rounded @error('password') border-red-500 @enderror">
                @error('password')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-4">
                <label for="password-confirm" class="block text-gray-700">Confirmar Contrasenya</label>
                <input id="password-confirm" type="password" name="password_confirmation" required class="w-full p-2 border rounded">
            </div>
            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Restablir Contrasenya
            </button>
            @if (Route::has('login'))
                <a href="{{ route('login') }}" class="block text-center text-blue-500 mt-2">Tornar a Iniciar Sessió</a>
            @endif
        </form>
    </div>
    </body>
</html>