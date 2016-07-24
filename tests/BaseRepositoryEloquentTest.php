<?php

class BaseRepositoryEloquentTest extends TestCase
{
    protected $repo;

    public function setUp()
    {
        parent::setUp();

        $table = "`{$this->db['database']}`.`spies`";

        $this->pdoExec("DROP TABLE IF EXISTS {$table}");
        
        $this->pdoExec("
            CREATE TABLE IF NOT EXISTS {$table} (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `username` varchar(255) NULL COMMENT '',
              `password` varchar(255) NULL COMMENT '',
              `name` varchar(255) NOT NULL COMMENT '',
              `xp` int(10) unsigned DEFAULT NULL COMMENT '',
              `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
              `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ");

        $this->repo = new SpyRepositoryEloquent(new Spy);
    }

    public function testShouldCreateStoreFindAndUpdateModel()
    {
        $spy = $this->repo->create([
            'username' => 'ajcastro',
            'password' => 'password',
            'name' => 'arjon',
            'xp' => 99
        ]);

        $storedSpy = $this->repo->find($spy->id);

        $this->assertTrue($spy instanceof Spy);
        $this->assertTrue($storedSpy instanceof Spy);
        $this->assertEquals($storedSpy->getAttributes(), $spy->getAttributes());

        $storedSpy->name = 'ajcastro';

        $this->repo->update(['name' => $storedSpy->name], $storedSpy->id);

        $updatedSpy = $this->repo->find($storedSpy->id);
        $this->assertEquals($updatedSpy->getAttributes(), $storedSpy->getAttributes());
    }

    public function seed()
    {
        $spies = [
            [
                'username' => 'markii1607',
                'password' => 'secret',
                'name' => 'mark',
                'xp' => 172
            ], [
                'username' => 'katbritanico',
                'password' => 'secret',
                'name' => 'katrina',
                'xp' => 57
            ], [
                'username' => 'janelagatuz',
                'password' => 'secret',
                'name' => 'janelle',
                'xp' => 352
            ],
        ];
        foreach ($spies as $spy) {
            $this->repo->create($spy);
        }
    }

    public function testShouldFetchWithGivenAttributes()
    {
        $this->seed();

        $attributes = [
            'username',
            'xp'
        ];

        $fetchSpies = $this->repo->fetch($attributes);

        $this->assertTrue(count($fetchSpies) > 0, 'No fetched record of spies.');

        foreach ($fetchSpies as $fetchSpy) {
            $this->assertEquals(array_keys($fetchSpy->getAttributes()), $attributes);
        }
    }

    public function testFetchFiltersUsingEquals()
    {
        $this->seed();

        // Test `=` with single value
        $spies = $this->repo->fetch(null, ['xp' => [
            '=' => 352
        ]]);

        $this->assertEquals(1, $spies->count());

        // Test `=` with array of values
        $spies = $this->repo->fetch(null, ['xp' => [
            '=' => [352]
        ]]);

        $this->assertEquals(1, $spies->count());

        $spies = $this->repo->fetch(null, ['xp' => [
            '=' => [352, 57]
        ]]);

        $this->assertEquals(2, $spies->count());

        $spies = $this->repo->fetch(null, ['xp' => [
            '=' => [352, 57, 172]
        ]]);

        $this->assertEquals(3, $spies->count());
    }

    public function testFetchFilterUsingOtherOperators()
    {
        $this->seed();

        // Test `!=`
        $spies = $this->repo->fetch(null, ['xp' => [
            '!=' => [352]
        ]]);

        $this->assertEquals(2, $spies->count());

        // Test `>`
        $spies = $this->repo->fetch(null, ['xp' => [
            '>' => [100]
        ]]);

        $this->assertEquals(2, $spies->count());

        // Test `<`
        $spies = $this->repo->fetch(null, ['xp' => [
            '<' => [100]
        ]]);

        $this->assertEquals(1, $spies->count());

        // Test for `>=` and `<=`
        $spies = $this->repo->fetch(null, ['xp'=> [
            '>=' => [180]
        ]]);

        $this->assertEquals(1, $spies->count());

        $spies = $this->repo->fetch(null, ['xp'=> [
            '<=' => [172]
        ]]);

        $this->assertEquals(2, $spies->count());
    }

    public function testFetchSortAscAndDesc()
    {
        $this->seed();

        // Test for name in ascending order
        $spies=$this->repo->fetch(null, null, [
            'name' => 'asc'
        ]);

        $this->assertEquals($spies->first()->name, "janelle");
        $this->assertEquals($spies->last()->name, "mark");

        // Test for xp in descending order
        $spies=$this->repo->fetch(null, null, [
            'xp' => 'desc'
        ]);

        $this->assertEquals($spies->first()->xp, 352);
        $this->assertEquals($spies->last()->xp, 57);
    }

    public function testFetchLimitAndSkip()
    {
        $this->seed();

        // Test with limit of 2
        $spies=$this->repo->fetch(null, null, null, 2);

        $this->assertTrue(count($spies)==2, "Spies collected: ".count($spies));

        // Test with limit 1
        $spies=$this->repo->fetch(null, null, null, 1);

        $this->assertTrue(count($spies)==1, "Spies collected: ".count($spies));

        // Test with limit 2 offset 1
        $spies=$this->repo->fetch(null, null, null, 2, 1);

        $this->assertTrue(count($spies)==2, "Spies collected: ".count($spies));
        $this->assertEquals($spies->first()->id, 2);

        // Test with limit 1 offset 2
        $spies=$this->repo->fetch(null, null, null, 1, 2);

        $this->assertTrue(count($spies)==1, "Spies collected: ".count($spies));
        $this->assertEquals($spies->first()->id, 3);
    }

    public function testFetchForAllParameters()
    {
        $this->seed();

        $attributes = [
            'username',
            'name'
        ];

        $filters = [
            'xp' => [
                '=' => [
                    352, 
                    57
                ]
            ]
        ];

        $sort = [
            'name' => 'desc'
        ];

        $spies=$this->repo->fetch($attributes, $filters, $sort, 1, 1);

        $this->assertTrue(count($spies) == 1, "Spies Count: ".count($spies));

        foreach ($spies as $spy) {
            $this->assertEquals(array_keys($spy->getAttributes()), $attributes);
        }

        $this->assertEquals($spies->first()->name, "janelle");
    }

    public function testPagination()
    {
        $this->seed();

        $attributes = [
            'username',
            'name'
        ];

        $filters = [
            'xp' => [
                '=' => [
                    352, 
                    57
                ]
            ]
        ];

        $sort = [
            'name' => 'desc'
        ];

        $spies = $this->repo->paginate($attributes, $filters, $sort, 2, 1);

        $this->assertTrue(count($spies) == 2, "Spies collected: "+count($spies));
        $this->assertEquals($spies[0]->getAttributes()['name'], "katrina");
    }
}