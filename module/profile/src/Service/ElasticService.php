<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Profile\Service;

use Cradle\Module\Profile\Service;

use Elasticsearch\Client as Resource;

use Elasticsearch\Common\Exceptions\NoNodesAvailableException;

use Cradle\Module\Utility\Service\ElasticServiceInterface;
use Cradle\Module\Utility\Service\AbstractElasticService;

/**
 * Profile ElasticSearch Service
 *
 * @vendor   Acme
 * @package  profile
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class ElasticService extends AbstractElasticService implements ElasticServiceInterface
{
    /**
     * @const INDEX_NAME Index name
     */
    const INDEX_NAME = 'profile';

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
     * Create in index
     *
     * @param *int $id
     *
     * @return array
     */
    public function create($id)
    {
        $body = $this->sql->get($id);

        if (!is_array($body) || empty($body)) {
            return false;
        }

        $body['profile_name'] = utf8_encode($body['profile_name']);

        // Checks if profile_company is null
        if ($body['profile_company'] === null) {
            $body['profile_company'] = '_null_';
        }

        // Checks if profile_company is empty string
        if ($body['profile_company'] === '') {
            $body['profile_company'] = '_empty_';
        }

        // Checks if profile_company is all whitespaces
        if (strlen($body['profile_company']) > 0
            && strlen(trim($body['profile_company'])) == 0) {
            $body['profile_company'] = '_empty_';
        }

        // Checks if profile_package is an empty array
        if (empty($body['profile_package'])) {
            $body['profile_package'] = null;
        }

        // Checks if profile_achievements is an empty array
        if (empty($body['profile_achievements'])) {
            $body['profile_achievements'] = null;
        }

        // Checks if profile_tags is an empty array
        if (empty($body['profile_tags'])) {
            $body['profile_tags'] = null;
        }

        // Checks if profile_story is an empty array
        if (empty($body['profile_story'])) {
            $body['profile_story'] = null;
        }

        // Checks if profile_interviewer is an empty array
        if (empty($body['profile_interviewer'])) {
            $body['profile_interviewer'] = null;
        }

        // Checks if profile_campaigns is an empty array
        if (empty($body['profile_campaigns'])) {
            $body['profile_campaigns'] = null;
        }

        // Checks for profile meta / profile_meta
        if (!isset($body['profile_meta']) || empty($body['profile_meta'])) {
            $body['profile_meta'] = null;
        }

        try {
            return $this->resource->index([
                'index' => static::INDEX_NAME,
                'type' => static::INDEX_TYPE,
                'id' => $id,
                'body' => $body
            ]);
        } catch (NoNodesAvailableException $e) {
            return false;
        } catch (BadRequest400Exception $e) {
            return false;
        }
    }

    /**
     * Create in index
     *
     * @param *int $id
     *
     * @return array
     */
    public function update($id)
    {
        $body = $this->sql->get($id);

        if (!is_array($body) || empty($body)) {
            return false;
        }

        // Checks if profile_company is null
        if ($body['profile_company'] === null) {
            $body['profile_company'] = '_null_';
        }

        // Checks if profile_company is empty string
        if ($body['profile_company'] === '') {
            $body['profile_company'] = '_empty_';
        }

        // Checks if profile_company is all whitespaces
        if (strlen($body['profile_company']) > 0
            && strlen(trim($body['profile_company'])) == 0) {
            $body['profile_company'] = '_empty_';
        }

        // Checks if profile_package is an empty array
        if (empty($body['profile_package'])) {
            $body['profile_package'] = null;
        }


        // Checks if profile_achievements is an empty array
        if (empty($body['profile_achievements'])) {
            $body['profile_achievements'] = null;
        }

        // Checks if profile_tags is an empty array
        if (empty($body['profile_tags'])) {
            $body['profile_tags'] = null;
        }

        // Checks if profile_story is an empty array
        if (empty($body['profile_story'])) {
            $body['profile_story'] = null;
        }

        // Checks if profile_campaigns is an empty array
        if (empty($body['profile_campaigns'])) {
            $body['profile_campaigns'] = null;
        }

        try {
            return $this->resource->update(
                [
                    'index' => static::INDEX_NAME,
                    'type' => static::INDEX_TYPE,
                    'id' => $id,
                    'body' => [
                        'doc' => $body
                    ]
                ]
            );
        } catch (Missing404Exception $e) {
            return false;
        } catch (NoNodesAvailableException $e) {
            return false;
        }
    }

    /**
     * Get detail from index
     *
     * @param *int|string $id
     *
     * @return array
     */
    public function get($id)
    {
        try {
            $results = $this->resource->get([
                'index' => static::INDEX_NAME,
                'type' => static::INDEX_TYPE,
                'id' => $id
            ]);
        } catch (Missing404Exception $e) {
            return null;
        } catch (NoNodesAvailableException $e) {
            return false;
        }

        $result = $results['_source'];

        if ($result['profile_company'] == '_null_'
            || $result['profile_company'] == '_empty_') {
            $result['profile_company'] = null;
        }

        return $result;
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
        $order = ['profile_id' => 'asc'];
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

        if (isset($data['not_filter']) && is_array($data['not_filter'])) {
            $notFilter = $data['not_filter'];
        }

        if (isset($data['like_filter']) && is_array($data['like_filter'])) {
            $likeFilter = $data['like_filter'];
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
                $keyword = trim($keyword);
                $keyword = preg_replace('/\//', '', $keyword);

                $fields = array(
                    'profile_name',
                    'profile_email',
                    'profile_phone',
                    'profile_company'
                );

                if (!is_numeric($keyword)) {
                    // Checks if the first letter is not part of the alphabet
                    if (!ctype_alnum(substr($keyword, 0, 1))) {
                        $keyword = substr($keyword, 1);
                    }

                    // Checks if the last character is not a part of the alphabet
                    if (!ctype_alnum(substr($keyword, (strlen($keyword) - 1), 1))) {
                        $keyword = substr($keyword, 0, (strlen($keyword) - 1));
                    }

                    // Allow for wildcard searching
                    $keyword = '*' . $keyword . '*';
                } else {
                    $fields = array(
                        'profile_id',
                    );
                }

                $search['query']['bool']['filter'][]['query_string'] = [
                    'query' => $keyword ,
                    'fields' => $fields,
                    'default_operator' => 'OR'
                ];
            }
        }

        // Checks if there is a type filter
        if (isset($filter['type'])) {
            if ($filter['type'] == 'seeker') {
                $search['query']['bool']['must'][]['query_string'] = [
                    'query' => '_null_, _empty_',
                    'fields' => ['profile_company'],
                    'default_operator' => 'OR'
                ];
            } elseif ($filter['type'] == 'poster') {
                $notFilter['profile_company'] = array('_empty_', '_null_');
                $search['query']['bool']['must_not'][]['query_string'] = [
                    'query' => '""',
                    'fields' => ['profile_company']
                ];
            }

            unset($filter['type']);
        }

        if (isset($data['crawled'])) {
            $notFilter['auth_id'] = null;
            $notFilter['profile_company'] = array('_empty_', '_null_');
                $search['query']['bool']['must_not'][]['query_string'] = [
                    'query' => '""',
                    'fields' => ['profile_company']
                ];
        }

        // Checks if the notNullJson is not empty
        if (!empty($likeFilter)) {
            // Loops through the columns
            foreach ($likeFilter as $column => $value) {
                // Checks if the value is an array
                if (is_array($value)) {
                    // Loops through the content of the value
                    foreach ($value as $v) {
                        $search['query']['bool']['filter'][]['query_string'] = [
                            'query' => '*' . $v . '*',
                            'fields' => [$column],
                            'default_operator' => 'OR'
                        ];
                    }
                } else {
                    $search['query']['bool']['filter'][]['query_string'] = [
                        'query' => '*' . $value . '*',
                        'fields' => [$column],
                        'default_operator' => 'OR'
                    ];
                }
            }
        }

        // Checks for notFilter
        if (!empty($notFilter)) {
            // Loops through the notFilter list
            foreach ($notFilter as $column => $value) {
                // Checks if the value is an array
                if (is_array($value)) {
                    // Loops through the array
                    foreach ($value as $v) {
                        // Checks if the value being filtered out is null
                        if (empty($v)) {
                            $search['query']['bool']['must']['exists']['field'] = $column;
                        } else {
                            $search['query']['bool']['must_not'][]['query_string'] = [
                                'query' => $column . ':' . $v,
                            ];
                        }
                    }
                } else {
                    // Checks if the value being filtered out is null
                    if (empty($value)) {
                        $search['query']['bool']['must']['exists']['field'] = $column;
                    } else {
                        $search['query']['bool']['must_not'][]['query_string'] = [
                            'query' => $column . ':' . $value,
                        ];
                    }
                }
            }
        }

        //generic full match filters

        //profile_active
        if (!isset($filter['profile_active'])) {
            $filter['profile_active'] = 1;
        }

        // Checks if there is a filter for email
        if (isset($filter['profile_email'])) {
            $search['query']['bool']['filter'][]['query_string'] = [
                'query' => $filter['profile_email'],
                'fields' => ['profile_email'],
                'default_operator' => 'OR'
            ];

            // Unsets the profile email / profile_email
            unset($filter['profile_email']);
        }

        foreach ($filter as $key => $value) {
            $search['query']['bool']['filter'][]['term'][$key] = $value;
        }

        //add sorting
        foreach ($order as $sort => $direction) {
            $search['sort'] = [$sort => $direction];
        }

        if (isset($data['!profile_company'])) {
            $term = 'term';
            if (is_array($data['!profile_company'])) {
                $term = 'terms';
            }

            $search['query']['bool']['must_not'][$term]['profile_company'] = $data['!profile_company'];
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
            $row = $item['_source'];

            // Checks for empty profile_company
            if ($row['profile_company'] == '_null_'
                || $row['profile_company'] == '_empty_') {
                $row['profile_company'] = null;
            }

            $rows[] = $row;
        }

        //return response format
        return [
            'rows' => $rows,
            'total' => $results['hits']['total']
        ];
    }
}
