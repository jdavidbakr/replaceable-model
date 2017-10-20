<?php

class ReplacableModelTest extends Orchestra\Testbench\TestCase {

    public function setUp()
    {
        parent::setUp();
        $this->artisan('migrate',['--path'=>'../../../../test-migrations']);
    }

    /**
     * @test
     */
    public function it_executes_replace_to_create_a_new_model()
    {
        $inserts = [
            [
                'id'=>1,
                'string'=>'string-1'
            ],
        ];
        $model = TestModel::replace($inserts);
        $this->assertDatabaseHas('test_models',[
            'id'=>1,
            'string'=>'string-1'
        ]);
        // Check for timestamp
        $this->assertDatabaseMissing('test_models', [
            'id'=>1,
            'created_at'=>null,
            'updated_at'=>null,
        ]);
    }
    
    /**
     * @test
     */
    public function it_executes_replace_to_update_and_create()
    {
        TestModel::create([
            'id'=>1,
            'string'=>'string-1',
        ]);
        $inserts = [
            [
                'id'=>1,
                'string'=>'string-2'
            ],
            [
                'id'=>2,
                'string'=>'string-3'
            ]
        ];
        $model = TestModel::replace($inserts);
        $this->assertDatabaseHas('test_models',[
            'id'=>1,
            'string'=>'string-2'
        ]);
        $this->assertDatabaseHas('test_models',[
            'id'=>2,
            'string'=>'string-3'
        ]);
        // Check for timestamp
        $this->assertDatabaseMissing('test_models', [
            'id'=>1,
            'created_at'=>null,
            'updated_at'=>null,
        ]);
        $this->assertDatabaseMissing('test_models', [
            'id'=>2,
            'created_at'=>null,
            'updated_at'=>null,
        ]);
    }

    /**
     * @test
     */
    public function it_executes_insert_ignore_to_create_a_new_model()
    {
        $inserts = [
            [
                'id'=>1,
                'string'=>'string-1'
            ],
        ];
        $model = TestModel::insertIgnore($inserts);
        $this->assertDatabaseHas('test_models',[
            'id'=>1,
            'string'=>'string-1'
        ]);
        // Check for timestamp
        $this->assertDatabaseMissing('test_models', [
            'id'=>1,
            'created_at'=>null,
            'updated_at'=>null,
        ]);
    }

    
    /**
     * @test
     */
    public function it_executes_insert_ignore_to_update_and_create()
    {
        TestModel::create([
            'id'=>1,
            'string'=>'string-1',
        ]);
        $inserts = [
            [
                'id'=>1,
                'string'=>'string-2'
            ],
            [
                'id'=>2,
                'string'=>'string-3'
            ]
        ];
        $model = TestModel::insertIgnore($inserts);
        // First one should not change
        $this->assertDatabaseHas('test_models',[
            'id'=>1,
            'string'=>'string-1'
        ]);
        // Second one should be inserted
        $this->assertDatabaseHas('test_models',[
            'id'=>2,
            'string'=>'string-3'
        ]);
        // Check for timestamp
        $this->assertDatabaseMissing('test_models', [
            'id'=>1,
            'created_at'=>null,
            'updated_at'=>null,
        ]);
        $this->assertDatabaseMissing('test_models', [
            'id'=>2,
            'created_at'=>null,
            'updated_at'=>null,
        ]);
    }
}

class TestModel extends Illuminate\Database\Eloquent\Model {
    
    use jdavidbakr\ReplaceableModel\ReplaceableModel;
    protected $guarded = [];

}