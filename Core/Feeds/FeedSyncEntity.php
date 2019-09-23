<?php
/**
 * FeedSyncEntity.
 *
 * @author emi
 */

namespace Minds\Core\Feeds;

use Minds\Traits\Exportable;
use Minds\Traits\MagicAttributes;

/**
 * Class FeedSyncEntity
 * @package Minds\Core\Feeds
 * @method int|string getGuid()
 * @method FeedSyncEntity setGuid(int|string $guid)
 * @method int|string getOwnerGuid()
 * @method FeedSyncEntity setOwnerGuid(int|string $ownerGuid)
 * @method int getTimestamp()
 * @method FeedSyncEntity setTimestamp(int $timestamp)
 * @method string getUrn()
 * @method FeedSyncEntity setUrn(string $urn)
 * @method int getAccessId()
 * @method FeedSyncEntity setAccessId(int $accessId)
 * @method string getType()
 * @method FeedSyncEntity setType(string $type)
 */
class FeedSyncEntity
{
    use MagicAttributes;

    /** @var int|string */
    protected $guid;

    /** @var int|string */
    protected $ownerGuid;

    /** @var int */
    protected $timestamp;

    /** @var int */
    protected $accessId;

    /** @var string */
    protected $urn;

    /** @var Entity */
    protected $entity;

    public function setEntity($entity)
    {
        $this->entity = $entity;
        $this->accessId = $entity->getAccessId();
        $this->type = $entity->getType();
    }

    /** @var type */
    protected $type;
    /**
     * Export to public API
     * @return array
     */
    public function export()
    {
        return [
            'guid' => (string) $this->guid,
            'owner_guid' =>  (string) $this->ownerGuid,
            'access_id' => $this->accessId,
            'timestamp' => $this->timestamp,
            'urn' => $this->urn,
            'type' => $this->type,
            'entity' => $this->entity ? $this->entity->export() : null,
        ];
    }
}
