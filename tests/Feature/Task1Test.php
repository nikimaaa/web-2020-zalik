<?php

namespace Tests\Feature;

use App\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\Response;
use Tests\TestCase;

class Task1Test extends TestCase
{
    use WithoutMiddleware, RefreshDatabase;

    protected $modelFields = [
        "first_name",
        "last_name",
        "middle_name"
    ];
    protected $modelClass = Person::class;
    protected $modelPluralName = "persons";
    protected $modelSingleName = "person";


    /* Checks json pagination */
    public function testIndex()
    {
        factory($this->modelClass, 50)->create();
        $per_page = rand(5, 15);
        $routeName = $this->modelPluralName . ".index";
        $response = $this->getJson(route($routeName, ['per_page' => $per_page]));
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure(["meta", "links", "data" => [$this->modelFields]]);
        $responseContent = $response->json();
        $this->assertCount($per_page, $responseContent["data"]);
        $this->assertEquals(50, $responseContent["meta"]["total"]);
    }

    /* Checks model creating */
    public function testCreate()
    {
        $routeName = $this->modelPluralName . ".create";
        $response = $this->get(route($routeName));
        $response->assertViewIs($routeName);
        $response->assertSee($this->modelPluralName . " form");
    }

    /* Checks json model deleting error*/
    public function testDeleteError()
    {
        $routeName = $this->modelPluralName . ".destroy";
        $response = $this->deleteJson(route($routeName, [$this->modelSingleName => 1]));
        $response->assertStatus(Response::HTTP_NOT_FOUND);
        $response->assertJson(['message' => "Not found"]);
    }

    /* Checks json model deleting */
    public function testDelete()
    {
        $model = factory(Person::class)->create();
        $routeName = $this->modelPluralName . ".destroy";
        $response = $this->deleteJson(route($routeName, [$this->modelSingleName => $model->id]));
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => $this->modelFields
        ]);
    }


}