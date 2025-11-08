<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class ApiClient {
    private PendingRequest $http;

    public function __construct() {
        // set di config/services.php → env('GO_API_BASE')
        $base = rtrim(config('services.go.base'), '/');
        $this->http = Http::baseUrl($base)->timeout(10)->acceptJson();
    }

    public function nationalities(): Response                 { return $this->http->get('/nationalities'); }
    public function listUsers(array $params = []): Response    { return $this->http->get('/users', $params); }
    public function getUser(int $id): Response                 { return $this->http->get("/users/{$id}"); }
    public function createUser(array $payload): Response       { return $this->http->post('/users', $payload); }
    public function updateUser(int $id, array $p): Response    { return $this->http->put("/users/{$id}", $p); }
    public function deleteUser(int $id): Response              { return $this->http->delete("/users/{$id}"); }
}
