<?php

namespace Spec\Minds\Core\Search;

use Minds\Core\Data\ElasticSearch\Client;
use Minds\Core\Data\ElasticSearch\Prepared\Index;
use Minds\Core\Data\ElasticSearch\Prepared\Update;
use Minds\Core\Di\Di;
use Minds\Core\Search\Mappings\Factory;
use Minds\Core\Search\Mappings\MappingInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class IndexSpec extends ObjectBehavior
{
    protected $_client;
    protected $_index = 'phpspec';
    protected $_mappingsFactory;

    public function let(
        Client $client,
        Factory $mappingsFactory
    ) {
        $this->_client = $client;
        $this->_mappingsFactory = $mappingsFactory;

        $this->beConstructedWith($client, $this->_index);

        Di::_()->bind('Search\Mappings', function ($di) use ($mappingsFactory) {
            return $mappingsFactory->getWrappedObject();
        });
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Minds\Core\Search\Index');
    }

    public function it_should_index(
        \ElggEntity $entity,
        MappingInterface $mapper
    ) {
        $this->_mappingsFactory->build($entity)
            ->shouldBeCalled()
            ->willReturn($mapper);

        $mapper->map()
            ->shouldBeCalled()
            ->willReturn([
                'guid' => '1000',
                'type' => 'test'
            ]);

        $mapper->suggestMap()
            ->shouldBeCalled()
            ->willReturn([
                'input' => [ 'test' ]
            ]);

        $mapper->getType()
            ->shouldBeCalled()
            ->willReturn('test');

        $mapper->getId()
            ->shouldBeCalled()
            ->willReturn('1000');

        $this->_client->request(Argument::that(function ($prepared) {
            if (!($prepared instanceof Update)) {
                return false;
            }

            $query = $prepared->build();

            return
                $query['index'] == $this->_index &&
                $query['type'] == 'test' &&
                $query['id'] == '1000' &&
                isset($query['body']) &&
                $query['body']['doc']['guid'] == '1000' &&
                $query['body']['doc']['type'] == 'test' &&
                isset($query['body']['doc']['suggest'])
            ;
        }))
            ->shouldBeCalled()
            ->willReturn(true);

        $this
            ->index($entity)
            ->shouldReturn(true);
    }

    public function it_should_return_false_during_index_if_no_entity()
    {
        $this
            ->index(null)
            ->shouldReturn(false);
    }
}
