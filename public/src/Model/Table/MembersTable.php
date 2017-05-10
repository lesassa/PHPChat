<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Members Model
 *
 * @method \App\Model\Entity\Member get($primaryKey, $options = [])
 * @method \App\Model\Entity\Member newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Member[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Member|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Member patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Member[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Member findOrCreate($search, callable $callback = null)
 */
class MembersTable extends Table
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

        $this->table('members');
        $this->displayField('memberName');
        $this->primaryKey('memberId');

//         $this->belongsTo('Types', [
//         		'className' => 'Types',
//         		'foreignKey' => 'typeId',
//         ]);
    }

    public function findActive(Query $query, array $options)
    {
    	return $query->find("all")->where(['deleteFrag =' => 0]);
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
            ->notEmpty('memberId', 'create');

        $validator
            ->notEmpty('memberName')
            ->add('memberName', 'length', [
				'rule' => ['maxLength', 32],
				'message' => '名前が長すぎます。']);

        $validator
        	->add('memberKana', 'format', [
        		'rule' => ['maxLength', 32],
        		'message' => 'ふりがなが長すぎます。',])
            ->notEmpty('memberKana');

        $validator
            ->integer('typeId')
            ->notEmpty('typeId');

        $validator
	        ->add('memberMail', 'format', [
        		'rule' => ['maxLength', 200],
        		'message' => 'メールアドレスが長すぎます。',])
        	->add('memberMail', 'validFormat', [
        		'rule' => 'email',
        		'message' => 'メールアドレスの形式が不正です。'])
            ->allowEmpty('memberMail');

        $validator
            ->dateTime('createDate')
            ->allowEmpty('createDate');

        $validator
            ->dateTime('updateDate')
            ->allowEmpty('updateDate');

        $validator
            ->integer('updateMemberId')
            ->notEmpty('updateMemberId');

        $validator
            ->allowEmpty('deleteFrag');

        return $validator;
    }
}
