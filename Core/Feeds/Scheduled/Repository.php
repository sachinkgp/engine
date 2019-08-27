<?php

namespace Minds\Core\Feeds\Scheduled;

use Minds\Core\Data\ElasticSearch\Client as ElasticsearchClient;
use Minds\Core\Data\ElasticSearch\Prepared;
use Minds\Core\Di\Di;
use Minds\Helpers\Text;

class Repository
{
    /** @var ElasticsearchClient */
    protected $client;

    protected $index;

    public function __construct($client = null, $config = null)
    {
        $this->client = $client ?: Di::_()->get('Database\ElasticSearch');

        $config = $config ?: Di::_()->get('Config');

        $this->index = $config->get('elasticsearch')['index'];
    }

    public function getScheduledCount(array $opts = [])
    {
        $opts = array_merge([
            'container_guid' => null,
            'type' => null,
        ], $opts);

        if (!$opts['type']) {
            throw new \Exception('Type must be provided');
        }

        if (!$opts['container_guid']) {
            throw new \Exception('Container Guid must be provided');
        }

        $containerGuids = Text::buildArray($opts['container_guid']);
        $query = [
            'index' => $this->index,
            'type' => $opts['type'],
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'range' => [
                                    'time_created' => [
                                        'gt' => time(),
                                    ]
                                ]
                            ],
                            [
                                'terms' => [
                                    'container_guid' => $containerGuids,
                                ],
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $prepared = new Prepared\Count();
        $prepared->query($query);

        $result = $this->client->request($prepared);

        return $result['count'] ?? 0;
    }
}
