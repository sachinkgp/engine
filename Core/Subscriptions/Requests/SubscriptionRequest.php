<?php
/**
 * SubscriptionRequest Model
 */
namespace Minds\Core\Subscriptions\Requests;

use Minds\Traits\MagicAttributes;

/**
 * @method SubscriptionRequest setPublisherGuid(string $publisherGuid)
 * @method string getPublisherGuid()
 * @method SubscriptionRequest setSubscriberGuid(string $subscriberGuid)
 * @method string getSubscriberGuid()
 * @method SubscriptionRequest setAccepted(bool $accepted)
 * @method string getAccepted()
 * @method SubscriptionRequest setTimestampMs(int $timestampMs)
 * @method int getTimestampMs()
 */
class SubscriptionRequest
{
    use MagicAttributes;

    /** @var string */
    private $publisherGuid;

    /** @var string */
    private $subscriberGuid;

    /** @var bool */
    private $accepted;

    /** @var int */
    private $timestampMs;

    /**
     * @return string
     */
    public function getUrn(): string
    {
        return "urn:subscription-request:" . implode('-', [ $this->publisherGuid, $this->subscriberGuid ]);
    }

    /**
     * Export
     * @return array
     */
    public function export(): array
    {
        return [
            'publisher_guid' => (string) $this->publisherGuid,
            'subscriber_guid' => (string) $this->subscriberGuid,
            'accepted' => (bool) $this->accepted,
            'timestamp_ms' => $this->timestampMs,
            'timestamp_sec' => round($this->timestampMs / 1000),
        ];
    }
}
