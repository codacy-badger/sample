<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Term\Service;

use Cradle\Module\Term\Service;

use Elasticsearch\Client as Resource;

use Elasticsearch\Common\Exceptions\NoNodesAvailableException;

use Cradle\Module\Utility\Service\ElasticServiceInterface;
use Cradle\Module\Utility\Service\AbstractElasticService;

/**
 * Term ElasticSearch Service
 *
 * @vendor   Acme
 * @package  term
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class ElasticService extends AbstractElasticService implements ElasticServiceInterface
{
    /**
     * @const INDEX_NAME Index name
     */
    const INDEX_NAME = 'term';

    /**
     * Registers the resource for use
     *
     * @param Resource $resource
     */
    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
        $this->sql = Service::get('sql');
    }

    /**
     * Search in index
     *
     * @param array $data
     *
     * @return array
     */
    public function search(array $data = [])
    {
        //set the defaults
        $filter = [];
        $range = 50;
        $start = 0;
        $order = ['term_id' => 'asc'];
        $count = 0;

        //merge passed data with default data
        if (isset($data['filter']) && is_array($data['filter'])) {
            $filter = $data['filter'];
        }

        if (isset($data['range']) && is_numeric($data['range'])) {
            $range = $data['range'];
        }

        if (isset($data['start']) && is_numeric($data['start'])) {
            $start = $data['start'];
        }

        if (isset($data['order']) && is_array($data['order'])) {
            $order = $data['order'];
        }

        //prepare the search object
        $search = [];

        
        //keyword search
        if (isset($data['q'])) {
            if (!is_array($data['q'])) {
                $data['q'] = [$data['q']];
            }

            foreach ($data['q'] as $keyword) {
                // TODO: need to cleanup keyword
                $keyword = preg_replace('/\//', ' ', $keyword);
                $search['query']['bool']['filter'][]['query_string'] = [
                    'query' => $keyword . '*',
                    'fields' => [
                        'term_name',
                    ],
                    'default_operator' => 'AND'
                ];
            }
        }
        

        //generic full match filters
        
        //term_active
        if (!isset($filter['term_active'])) {
            $filter['term_active'] = 1;
        }
        

        foreach ($filter as $key => $value) {
            $search['query']['bool']['filter'][]['term'][$key] = $value;
        }

        //add sorting
        foreach ($order as $sort => $direction) {
            $search['sort'] = [$sort => $direction];
        }

        try {
            $results = $this->resource->search([
                'index' => static::INDEX_NAME,
                'type' => static::INDEX_TYPE,
                'body' => $search,
                'size' => $range,
                'from' => $start
            ]);
        } catch (NoNodesAvailableException $e) {
            return false;
        }

        // fix it
        $rows = array();

        foreach ($results['hits']['hits'] as $item) {
            $rows[] = $item['_source'];
        }

        //return response format
        return [
            'rows' => $rows,
            'total' => $results['hits']['total']
        ];
    }

    /**
     * Search in index
     *
     * @param array $data
     *
     * @return array
     */
    public function fuzzy(array $data = [])
    {
        //set the defaults
        $start = 0;
        $range = 50;
        $filter = [];
        $count = 0;
        $fuzziness = 2;
        $prefix = 1;

        if (isset($data['fuzziness']) && is_numeric($data['fuzziness'])) {
            $fuzziness = $data['fuzziness'];
        }

        if (isset($data['prefix']) && is_numeric($data['fuzziness'])) {
            $prefix = $data['prefix'];
        }

        //prepare the search object
        // TODO: need to cleanup term
        $data['term'] = preg_replace('/\//', '', $data['term']);
        $search = ['query' =>
            ['match' => [
                'term_name' => [
                    'query' => $data['term'],
                    'fuzziness' => $fuzziness,
                    'prefix_length' => $prefix
                    ]
                ]
            ]
        ];
        
        try {
            $results = $this->resource->search([
                'index' => static::INDEX_NAME,
                'type' => static::INDEX_TYPE,
                'body' => $search,
                'size' => $range,
                'from' => $start
            ]);
        } catch (NoNodesAvailableException $e) {
            return false;
        }
        
        // fix it
        $rows = array();

        foreach ($results['hits']['hits'] as $item) {
            $rows[] = $item['_source'];
        }
        
        //return response format
        return [
            'rows' => $rows,
            'total' => $results['hits']['total']
        ];
    }
}
