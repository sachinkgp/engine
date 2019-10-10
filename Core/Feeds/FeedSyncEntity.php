<?php
/**
 * FeedSyncEntity.
 *
 * @author emi
 */

namespace Minds\Core\Feeds;

use JsonSerializable;
use Minds\Entities\Entity;
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
 * @method Entity getEntity()
 */
class FeedSyncEntity implements JsonSerializable
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

    /**
     * Setter for populating the child entity on feed sync items
     * Some FeedSyncEntity managers populate the entity with the hydrated object
     * Do not assume this is always called because you can easily
     * Just have the header information stored in ES (without the entity)
     */
    public function setEntity($entity) : FeedSyncEntity
    {
        $this->entity = $entity;
        $this->accessId = $entity->getAccessId();
        $this->type = $entity->getType();
        return $this;
    }

    /** @var type */
    public $type;

    /**
     * Export to public API
     * @return array
     */
    public function export()
    {
        return [
            'guid' => (string) $this->guid,
            'owner_guid' => (string) $this->ownerGuid,
            'access_id' => (int) $this->accessId,
            'timestamp' => $this->timestamp,
            'urn' => $this->urn,
            'type' => $this->type,
            'entity' => $this->entity ? $this->entity->export() : null,
        ];
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): array
    {
        return $this->export();
    }
}
