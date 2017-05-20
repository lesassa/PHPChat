<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Login Model
 *
 * @method \App\Model\Entity\Login get($primaryKey, $options = [])
 * @method \App\Model\Entity\Login newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Login[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Login|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Login patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Login[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Login findOrCreate($search, callable $callback = null)
 */
class LoginTable extends Table
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

        $this->table('login');
        $this->displayField('loginId');
        $this->primaryKey('loginId');
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
            ->notEmpty('loginId', 'create');

        $validator
            ->notEmpty('password')
            ->add('password', 'comWith',[
                    'rule' => ['compareWith', "passwordCheck"],
                    'message' => '確認用と相違があります。']);

        $validator
            ->integer('memberId')
            ->notEmpty('memberId');

        return $validator;
    }

    // テーブルクラスの中で
    public function buildRules(RulesChecker $rules)
    {

    	$rules->addUpdate(function ($entity, $options) {
    		return false;
    	}, 'duplication',
    	['errorField' => 'loginId', 'message' => 'ログインIDが重複しています。']);
    	return $rules;
    }
}
