<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Auth\DefaultPasswordHasher;

/**
 * Login Entity
 *
 * @property string $loginId
 * @property string $password
 * @property int $memberId
 */
class Login extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
//         'loginId' => false
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array
     */
    protected $_hidden = [
        'password'
    ];

    /**
     * パスワード保存時のハッシュ化
     * @param  string $password パスワード文字列
     * @return string           ハッシュ化されたパスワード
     */
    protected function _setPassword($password)
    {
    	$password = (new DefaultPasswordHasher)->hash($password);
    	return $password;
    }
}
