<?php
namespace App\Form\Reader;

use Cake\Form\Form;
use Cake\Form\Schema;
use Cake\Validation\Validator;

class IconForm extends Form
{


	protected function _buildSchema(Schema $schema)
	{
		// フィールドセット
		return $schema->addField('icon', ['type' => 'file'])
		;
	}

	protected function _buildValidator(Validator $validator)
	{
		//カスタムバリデーション読込
		$validator->provider('custom', 'App\Model\Validation\CustomValidation');

		// バリデーションセット
		return $validator->notEmpty('icon', "アップロードが選択されていません。")
				->add('icon', [
				'uploadedFile' => [
						'rule' => ['uploadedFile', ['maxSize' => '5MB']],
						'message' => 'ファイル最大サイズは5MB以内にしてください。'
				]])
				->add('icon.name', 'length', [
					'rule' => ['maxLength', 160],
					'message' => 'ファイル名は160文字以内にしてください。']);
				;
	}

	protected function _execute(array $data)
	{

		return true;
	}
}