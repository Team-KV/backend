<?php

namespace Tests\Unit;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\CreatesApplication;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use CreatesApplication, DatabaseMigrations, WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('db:seed');
    }

    public function test_create()
    {
        $response = $this->
        postJson('/api/category/', [
            'name' => 'Testovací kategorie',
            'color' => null]);

        $response->assertStatus(200)->
        assertJsonPath('Category.name', 'Testovací kategorie')->
        assertJsonPath('Category.color', null);
    }

    public function test_update()
    {
        $category = $this->createCategory();

        $response = $this->
        putJson('/api/category/'.$category->id, [
            'name' => 'Třísla',
            'color' => '#378B2E']);

        $response->assertStatus(200)->
        assertJsonPath('Category.name', 'Třísla')->
        assertJsonPath('Category.color', '#378B2E');
    }

    public function test_delete()
    {
        $category = $this->createCategory();

        $this->delete('/api/category/'.$category->id)->
        assertStatus(204);

        $this->get('/api/category/'.$category->id)->
        assertStatus(404)->
        assertJson(fn (AssertableJson $json) => $json->has('message'));
    }

    private function createCategory(): Category
    {
        return Factory::factoryForModel(Category::class)->create([
            'name' => 'Testovací kategorie',
            'color' => '#313975'
        ]);
    }
}
