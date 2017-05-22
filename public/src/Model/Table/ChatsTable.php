<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Chats Model
 *
 * @method \App\Model\Entity\Chat get($primaryKey, $options = [])
 * @method \App\Model\Entity\Chat newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Chat[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Chat|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Chat patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Chat[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Chat findOrCreate($search, callable $callback = null, $options = [])
 */
class ChatsTable extends Table
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

        $this->setTable('chats');
        $this->setDisplayField('roomId');
        $this->setPrimaryKey(['roomId', 'chatNumber']);

		$this->belongsTo('Members', [
			'className' => 'Members',
			'foreignKey' => 'memberId',
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
            ->integer('chatNumber')
            ->allowEmpty('chatNumber', 'create');

        $validator
            ->integer('memberId')
            ->allowEmpty('memberId');

        $validator
            ->notempty('chatText', "チャット内容が未入力です。")
            ->add('chatText', 'length', [
            	'rule' => ['maxLength', 140],
            	'message' => '140文字以内で入力してください。']);

        $validator
            ->integer('replyId')
            ->allowEmpty('replyId');

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
