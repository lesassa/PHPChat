<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Member Entity
 *
 * @property int $memberId
 * @property string $memberName
 * @property string $memberKana
 * @property int $typeId
 * @property string $memberMail
 * @property \Cake\I18n\Time $createDate
 * @property \Cake\I18n\Time $updateDate
 * @property int $updateMemberId
 * @property bool $deleteFrag
 */
class Member extends Entity
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
        'memberId' => false
    ];


}
