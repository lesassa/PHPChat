<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\NocaresTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\NocaresTable Test Case
 */
class NocaresTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\NocaresTable
     */
    public $Nocares;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.nocares'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Nocares') ? [] : ['className' => 'App\Model\Table\NocaresTable'];
        $this->Nocares = TableRegistry::get('Nocares', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Nocares);

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
