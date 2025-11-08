<?php

namespace Tests\Feature;

use App\Services\ApiClient;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\Client\Response as HttpResponse;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Mockery;
use Tests\TestCase;

final class UserUiControllerTest extends TestCase
{
    use WithoutMiddleware;

    /** @var \Mockery\MockInterface|\App\Services\ApiClient */
    private $api;

    protected function setUp(): void
    {
        parent::setUp();
        $this->api = Mockery::mock(ApiClient::class);
        $this->app->instance(ApiClient::class, $this->api);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** Helper: buat Illuminate\Http\Client\Response beneran */
    private function resp(int $status, array $body = []): HttpResponse
    {
        return new HttpResponse(
            new GuzzleResponse(
                $status,
                ['Content-Type' => 'application/json'],
                json_encode($body, JSON_UNESCAPED_UNICODE)
            )
        );
    }


    /** ====== STORE ====== */
    public function test_store_created_redirects_to_create_with_flash(): void
    {
        $payload = [
            'cst_name' => 'John',
            'cst_dob' => '1990-01-31',
            'nationality_id' => 1,
            'cst_phoneNum' => '08123',
            'cst_email' => 'john@doe.com',
            'family' => [
                ['fl_relation'=>'Spouse','fl_name'=>'Jane','fl_dob'=>'1992-05-10'],
            ],
        ];

        $this->api->shouldReceive('createUser')
            ->once()->with(Mockery::on(fn($p) => $p['cst_name'] === 'John'))
            ->andReturn($this->resp(201));

        $this->post('/users', $payload)
            ->assertRedirect(route('users.create'))
            ->assertSessionHas('ok', 'Created');
    }

    public function test_store_conflict_email_shows_error(): void
    {
        $payload = [
            'cst_name' => 'John',
            'cst_dob' => '1990-01-31',
            'nationality_id' => 1,
            'cst_phoneNum' => '08123',
            'cst_email' => 'dup@doe.com',
        ];

        $this->api->shouldReceive('createUser')
            ->once()->andReturn($this->resp(409));

        $this->post('/users', $payload)
            ->assertSessionHasErrors(['cst_email']);
    }

    public function test_store_api_validation_422_forwards_field_errors(): void
    {
        $payload = [
            'cst_name' => 'John',
            'cst_dob' => '1990-01-31',
            'nationality_id' => 1,
            'cst_phoneNum' => '08123',
            'cst_email' => 'john@doe.com',
        ];

        $this->api->shouldReceive('createUser')
            ->once()->andReturn($this->resp(422, [
                'fields' => ['cst_email' => 'taken'],
            ]));

        $this->post('/users', $payload)
            ->assertSessionHasErrors(['cst_email']);
    }


    /** ====== SHOW ====== */
    public function test_show_404_when_api_not_found(): void
    {
        $this->api->shouldReceive('getUser')
            ->once()->with(999)->andReturn($this->resp(404));

        $this->get('/users/999')->assertNotFound();
    }

    public function test_show_ok_displays_user(): void
    {
        $this->api->shouldReceive('getUser')
            ->once()->with(10)->andReturn($this->resp(200, [
                'cst_id'=>10,'cst_name'=>'Alice','cst_email'=>'a@ex.com','cst_phoneNum'=>'081'
            ]));

        $this->get('/users/10')
            ->assertOk();
    }

    /** ====== DESTROY ====== */
    public function test_destroy_success_redirects_to_create(): void
    {
        $this->api->shouldReceive('deleteUser')
            ->once()->with(10)->andReturn($this->resp(200, ['status'=>'ok']));

        $this->delete('/users/10')
            ->assertRedirect(route('users.create'))
            ->assertSessionHas('ok', 'Deleted');
    }

    public function test_destroy_failed_shows_error(): void
    {
        $this->api->shouldReceive('deleteUser')
            ->once()->with(10)->andReturn($this->resp(500));

        $this->delete('/users/10')
            ->assertSessionHasErrors(['api' => 'Delete failed']);
    }
}
