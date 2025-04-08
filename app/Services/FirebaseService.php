<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;

class FirebaseService
{
    protected Database $database;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(config('firebase.credentials'))
            ->withDatabaseUri(config('firebase.database_uri'));

        $this->database = $factory->createDatabase();
    }

    public function getDatabase(): Database
    {
        return $this->database;
    }

    public function set(string $path, array $data): void
    {
        $this->database->getReference($path)->set($data);
    }

    public function update(string $path, array $data): void
    {
        $this->database->getReference($path)->update($data);
    }

    public function delete(string $path): void
    {
        $this->database->getReference($path)->remove();
    }

    public function get(string $path)
    {
        return $this->database->getReference($path)->getValue();
    }
}
