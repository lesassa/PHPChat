<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\SubscribesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\SubscribesTable Test Case
 */
class SubscribesTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\SubscribesTable
     */
    public $Subscribes;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.subscribes'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Subscribes') ? [] : ['className' => 'App\Model\Table\SubscribesTable'];
        $this->Subscribes = TableRegistry::get('Subscribes', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Subscribes);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
