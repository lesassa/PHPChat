<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Rooms Model
 *
 * @method \App\Model\Entity\Room get($primaryKey, $options = [])
 * @method \App\Model\Entity\Room newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Room[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Room|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Room patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Room[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Room findOrCreate($search, callable $callback = null, $options = [])
 */
class RoomsTable extends Table
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

        $this->setTable('rooms');
        $this->setDisplayField('roomId');
        $this->setPrimaryKey('roomId');

        $this->belongsTo('Subscribes', [
        		'className' => 'Subscribes',
        		'foreignKey' => 'roomId',
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
            ->integer('roomId')
            ->allowEmpty('roomId', 'create');

        $validator
        	->notEmpty('roomName', "ルーム名が未入力です。")
            ->add('roomName', 'length', [
            		'rule' => ['maxLength', 32],
            		'message' => '32文字以内で入力してください。']);


        $validator
        	->notEmpty('roomDescription', "ルーム説明が未入力です。")
        	->add('roomDescription', 'length', [
        			'rule' => ['maxLength', 140],
        			'message' => '140文字以内で入力してください。']);


        $validator
            ->dateTime('createDate')
            ->allowEmpty('createDate');

        $validator
            ->dateTime('updateDate')
            ->allowEmpty('updateDate');

        $validator
            ->integer('updateMemberId')
            ->allowEmpty('updateMemberId');

        $validator
            ->boolean('deleteFrag')
            ->allowEmpty('deleteFrag');

        return $validator;
    }
}
