<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Nocares Model
 *
 * @method \App\Model\Entity\Nocare get($primaryKey, $options = [])
 * @method \App\Model\Entity\Nocare newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Nocare[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Nocare|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Nocare patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Nocare[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Nocare findOrCreate($search, callable $callback = null, $options = [])
 */
class NocaresTable extends Table
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

        $this->setTable('nocares');
        $this->setDisplayField('memberId');
        $this->setPrimaryKey(['memberId', 'roomId', 'chatNumber']);
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

        $validator
            ->integer('chatNumber')
            ->allowEmpty('chatNumber', 'create');

        return $validator;
    }
}
