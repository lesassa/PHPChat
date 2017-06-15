<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Chat Entity
 *
 * @property int $roomId
 * @property int $chatNumber
 * @property int $memberId
 * @property string $chatText
 * @property int $replyId
 * @property \Cake\I18n\Time $createDate
 * @property \Cake\I18n\Time $updateDate
 * @property int $updateMemberId
 * @property bool $deleteFrag
 */
class Chat extends Entity
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
    	'roomId' => true,
        'chatNumber' => false
    ];


    protected function _getChatTime()
    {
    	return $this->_properties['createDate']->format('Y/m/d H:i:s');
    }

}
