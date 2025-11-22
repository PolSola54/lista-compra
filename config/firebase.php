<?php
/*
return [
    'credentials' => env('FIREBASE_CREDENTIALS'),
    'database_uri' => env('FIREBASE_DATABASE_URI'),
];


return [
    'credentials' => env('FIREBASE_CREDENTIALS', base_path('storage/app/firebase/firebase_credentials.json')),
    'database_uri' => env('FIREBASE_DATABASE_URI'),
];
*/
/*
return [
'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase/firebase_credentials.json')),    
'database_uri' => env('FIREBASE_DATABASE_URI', 'https://lista-compra-pol1-default-rtdb.europe-west1.firebasedatabase.app/'),
];
*/

return [
    'credentials' => storage_path('app/firebase/firebase_credentials.json'),
    'database_uri' => env('FIREBASE_DATABASE_URI'),
];




