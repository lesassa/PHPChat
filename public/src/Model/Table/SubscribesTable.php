<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Subscribes Model
 *
 * @method \App\Model\Entity\Subscribe get($primaryKey, $options = [])
 * @method \App\Model\Entity\Subscribe newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Subscribe[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Subscribe|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Subscribe patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Subscribe[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Subscribe findOrCreate($search, callable $callback = null, $options = [])
 */
class SubscribesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('subscribes');
        $this->setDisplayField('memberId');
        $this->setPrimaryKey(['memberId', 'roomId']);

        $this->belongsTo('Rooms', [
        		'className' => 'Rooms',
        		'foreignKey' => 'roomId',
        ]);

        $this->belongsTo('Members', [
        		'className' => 'Members',
        		'foreignKey' => 'memberId',
        		'bindingKey' => "memberId",
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('memberId')
            ->allowEmpty('memberId', 'create');

        $validator
            ->integer('roomId')
            ->allowEmpty('roomId', 'create');

        return $validator;
    }
}
