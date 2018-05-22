<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Post\Service;

use Cradle\Module\Post\Service;

use Elasticsearch\Client as Resource;

use Elasticsearch\Common\Exceptions\NoNodesAvailableException;

use Cradle\Module\Utility\Service\ElasticServiceInterface;
use Cradle\Module\Utility\Service\AbstractElasticService;

/**
 * Post ElasticSearch Service
 *
 * @vendor   Acme
 * @package  post
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class ElasticService extends AbstractElasticService implements ElasticServiceInterface
{
    /**
     * @const INDEX_NAME Index name
     */
    const INDEX_NAME = 'post';

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

        $exact = array(
            'post_name',
            'post_position',
            'post_location'
        );

        if (!is_array($body) || empty($body)) {
            return false;
        }

        if (!empty($exact)) {
            // Loops through the fields
            foreach ($exact as $field) {
                $exactField = $field . '_exact';
                $body[$exactField] = $body[$field];
            }
        }

        // Checks for likers
        if (isset($body['likers'])) {
            unset($body['likers']);
        }

        // Checks for post meta / post_meta
        if (!isset($body['post_meta']) || empty($body['post_meta'])) {
            $body['post_meta'] = null;
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
     * Update in index
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

        // Checks for likers
        if (isset($body['likers'])) {
            unset($body['likers']);
        }

        $exact = array(
            'post_name',
            'post_position',
            'post_location'
        );

        if (!is_array($body) || empty($body)) {
            return false;
        }

        if (!empty($exact)) {
            // Loops through the fields
            foreach ($exact as $field) {
                $exactField = $field . '_exact';
                $body[$exactField] = $body[$field];
            }
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

        // Checks for post_notify
        if ($results['post_notify']) {
            $results['post_notify'] = json_decode($results['post_notify'], true);
        } else {
            $results['post_notify'] = [];
        }

        // Checks for post_tags
        if ($results['post_tags']) {
            $results['post_tags'] = json_decode($results['post_tags'], true);
        } else {
            $results['post_tags'] = [];
        }

        // Checks for post_geo_location
        if ($results['post_geo_location']) {
            $results['post_geo_location'] = json_decode($results['post_geo_location'], true);
        } else {
            $results['post_geo_location'] = [];
        }

        // Checks for post_package
        if (isset($results['post_package'])) {
            if ($results['post_package']) {
                $results['post_package'] = json_decode($results['post_package'], true);
            } else {
                $results['post_package'] = [];
            }
        }

        // Checks the post_type
        if ($results['post_type'] == 'poster') {
            $results['post_url'] = '/Company-Hiring/'
                . $this->slugify($results['post_position'], $results['post_id']);
        } else {
            $results['post_url'] = '/Seeking-Job/'
                . $this->slugify($results['post_position'], $results['post_id']);
        }

        //achievements
        if ($results['profile_achievements']) {
            $results['profile_achievements'] = json_decode($results['profile_achievements'], true);
        } else {
            $results['profile_achievements'] = [];
        }

        if ($results['profile_interviewer']) {
            $results['profile_interviewer'] = json_decode($results['profile_interviewer'], true);
        } else {
            $results['profile_interviewer'] = [];
        }

        if ($results['profile_package']) {
            $results['profile_package'] = json_decode($results['profile_package'], true);
        } else {
            $results['profile_package'] = [];
        }

        //tags
        if ($results['profile_tags']) {
            $results['profile_tags'] = json_decode($results['profile_tags'], true);
        } else {
            $results['profile_tags'] = [];
        }

        //story
        if ($results['profile_story']) {
            $results['profile_story'] = json_decode($results['profile_story'], true);
        } else {
            $results['profile_story'] = [];
        }

        //campaign
        if ($results['profile_campaigns']) {
            $results['profile_campaigns'] = json_decode($results['profile_campaigns'], true);
        } else {
            $results['profile_campaigns'] = [];
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
        $order = ['post_id' => 'asc'];
        $count = 0;
        $geoPoint = false;
        $geoLocation = false;

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

        if (isset($data['geo_point']) && !empty($data['geo_point'])) {
            $geoPoint['area_location'] = $data['geo_point'];
        }

        /// Variable declaration
        $notNullJson = array();

        //post_tags
        if (isset($data['post_tags']) && !empty($data['post_tags'])) {
            // Checks if not an array
            if (!is_array($data['post_tags'])) {
                $data['post_tags'] = [$data['post_tags']];
            }

            $notNullJson['post_tags'] = $data['post_tags'];
        }

        //post_notify
        if (isset($data['post_notify']) && !empty($data['post_notify'])) {
            // Checks if not an array
            if (!is_array($data['post_notify'])) {
                $data['post_notify'] = [$data['post_notify']];
            }

            $notNullJson['post_notify'] = $data['post_notify'];
        }

        //post_package
        if (isset($data['post_package']) && !empty($data['post_package'])) {
            // Checks if not an array
            if (!is_array($data['post_package'])) {
                $data['post_package'] = [$data['post_package']];
            }

            $notNullJson['post_package'] = $data['post_package'];
        }

        //prepare the search object
        $search = [];

        // Checks for location variable
        if (isset($data['location']) && !empty($data['location'])
            && !is_array($data['location'])) {
            $geoLocation = $data['location'];
            unset($data['location']);
        }

        // Checks for post_location variable
        if (isset($filter['post_location']) && !empty($filter['post_location'])
            && !is_array($filter['post_location'])) {
            $geoLocation = $filter['post_location'];
            unset($filter['post_location']);
        }

        // Checks for location variable
        if (isset($data['location']) && !empty($data['location'])
            && is_array($data['location'])) {
            $data['location'] = implode(',', $data['location']);
        }

        // Checks for post_location variable
        if (isset($filter['post_location']) && !empty($filter['post_location'])
            && is_array($filter['post_location'])) {
            $filter['post_location'] = implode(',', $filter['post_location']);
        }

        // Checks for post_location filter
        if ($geoLocation && !$geoPoint) {
            $geoLocation = $this->searchLocation($geoLocation);

            // Checks if there are rows
            if (!empty($geoLocation['rows'])) {
                // Loops through the rows
                foreach ($geoLocation['rows'] as $row) {
                    if ($geoPoint) {
                        continue;
                    }

                    // Checks if there are points saved
                    if (isset($row['area_location']) && !empty($row['area_location'])) {
                        $geoPoint = $row;
                    }
                }
            }
        }

        // Checks for post point filter
        if (isset($filter['post_point'])) {
            $geoPoint = $filter['post_point'];
            unset($filter['post_point']);
        }

        // Checks for geo point queires
        if ($geoPoint) {
            $search['query']['bool']['filter'][]['geo_distance'] = [
                'distance' => '50km',
                'post_geo_location' => $geoPoint['area_location']
            ];

            if (isset($data['location'])) {
                unset($data['location']);
            }

            if (isset($filter['post_location'])) {
                unset($filter['post_location']);
            }
        }

        // Checks for location like
        if (isset($data['location_like'])) {
            // Allow for wildcard searching
            $data['location_like'] = '*' . $data['location_like'] . '*';

            $search['query']['bool']['filter'][]['query_string'] = [
                'query'            => $data['location_like'],
                'fields'           => ['post_location_exact', 'post_tags'],
                'default_operator' => 'OR'
            ];
        }

        // Checks for position like
        if (isset($data['position_like'])) {
            // Allow for wildcard searching
            $data['position_like'] = '*' . $data['position_like'] . '*';

            $search['query']['bool']['filter'][]['query_string'] = [
                'query'            => $data['position_like'],
                'fields'           => ['post_position_exact', 'post_tags'],
                'default_operator' => 'OR'
            ];
        }

        //keyword search
        if (isset($data['q'])) {
            if (!is_array($data['q'])) {
                $data['q'] = [$data['q']];
            }

            foreach ($data['q'] as $keyword) {
                // TODO: need to cleanup keyword
                $keyword = trim($keyword);
                $keyword = preg_replace('/\//', '', $keyword);

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

                $search['query']['bool']['filter'][]['query_string'] = [
                    'query' => $keyword,
                    'fields' => [
                        'post_name_exact',
                        'post_position_exact',
                        'post_location_exact',
                        'post_detail',
                    ],
                    'default_operator' => 'OR'
                ];
            }
        }

        // Checks if the notNullJson is not empty
        if (!empty($notNullJson)) {
            // Loops through the columns
            foreach ($notNullJson as $column => $field) {
                $implode = array();

                // Loops through the content of the field
                foreach ($field as $value) {
                    $implode[] = '*'.$value.'*';
                }

                $search['query']['bool']['filter'][]['query_string'] = [
                    'query' => '('.implode(',', $implode).')',
                    'fields' => [$column],
                    'default_operator' => 'OR'
                ];
            }
        }

        // Checks for post_location
        if (isset($filter['post_location'])) {
            $likeFilter['post_location'] = $filter['post_location'];
            unset($filter['post_location']);
        }

        // Checks if the notNullJson is not empty
        if (!empty($likeFilter)) {
            // Loops through the columns
            foreach ($likeFilter as $column => $value) {
                // Checks if the value is an array
                if (is_array($value)) {
                    $implode = array();

                    // Loops through the content of the value
                    foreach ($value as $v) {
                        $implode[] = '*'.$v.'*';
                    }

                    $search['query']['bool']['filter'][]['query_string'] = [
                        'query' => '('.implode(',', $implode).')',
                        'fields' => [$column],
                        'default_operator' => 'OR'
                    ];
                } else {
                    $search['query']['bool']['filter'][]['query_string'] = [
                        'query' => '*' . $value . '*',
                        'fields' => [$column],
                        'default_operator' => 'OR'
                    ];
                }
            }
        }

        //generic full match filters
        // post_active
        if (!isset($filter['post_active'])) {
            $filter['post_active'] = 1;
        }

        // Checks for export
        if (isset($data['export'])) {
            $range = 0;
        }

        // Checks for today filter
        if (isset($data['today'])) {
            $search['query']['bool']['filter'][]['range']['post_created'] = [
                'gte' => 'now-1d/d',
                'lt'  => 'now+1d/d'];
        }

        // Checks for not expiring filter
        if (isset($data['not_expires'])) {
            $search['query']['bool']['filter'][]['range']['post_expires'] = ['gte' => 'now'];
        }

        // Checks for post_duplicate and post_active
        if (!isset($data['post_duplicate']) && isset($filter['post_active'])) {
            // Checks if post_expires doesnt exist
            if (!isset($data['post_expires'])) {
                $data['post_expires'] = 'now';
            }

            // Checks for expiring soon
            if ($data['post_expires'] === 'soon') {
                $search['query']['bool']['filter'][]['range']['post_expires'] = [
                    'gte' => 'now',
                    'lt'  => 'now+11d/d'
                ];
            } else {
                // Check if we're looking for anything recently created
                if ($data['post_expires'] === '-1') {
                     $search['query']['bool']['filter'][]['range']['post_expires']
                        = ['lt' => 'now'];
                } else {
                    // Gets the post_expires
                    $expires = $data['post_expires'];

                    // Checks if the post_expires contains words like year
                    if (strpos($expires, 'day') !== false
                        || strpos($expires, 'year') !== false) {
                        $expires = explode(' ', $expires);
                        $multiplier = 1;

                        // Checks if years
                        if (strpos($expires[1], 'year')) {
                            $multiplier = 365;
                        }

                        // Converts this to something elastic will understand when filtering date ranges
                        $expires = $expires[0] * $multiplier;
                        $expires = 'now'.$expires.'d/d';
                    }

                    $search['query']['bool']['filter'][]['range']['post_expires']
                        = ['gte' => $expires];
                }
            }
        }

        $exactSearch = array(
            'post_name',
            'post_position',
            'post_location'
        );

        $stringSearch = array(
            'post_name',
            'post_position',
            'post_location',
            'post_email',
            'profile_name',
            'profile_email',
            'profile_company',
            'profile_website',
            'profile_facebook',
            'profile_linkedin',
            'profile_twitter',
            'profile_google',
            'profile_billing_name',
            'profile_address_street',
            'profile_address_city',
            'profile_address_state',
            'profile_address_country',
        );

        foreach ($filter as $key => $value) {
            // Checks if this is a string search
            if (in_array($key, $stringSearch)) {
                // $value = '"'.$value.'"'
                // Checks if this is an exact search
                if (in_array($key, $exactSearch)) {
                    $key .= "_exact";
                }

                $search['query']['bool']['filter'][]['query_string'] = [
                    'query' => $value,
                    'fields' => [$key]
                ];
            } else {
                if ($key == 'post_flag' && is_array($value)) {
                    $search['query']['bool']['filter'][]['terms'][$key] = $value;
                } else {
                    $search['query']['bool']['filter'][]['term'][$key] = $value;
                }
            }
        }

        //add sorting
        foreach ($order as $sort => $direction) {
            $search['sort'] = [$sort => $direction];
        }

        // Special Sort for geopoint
        if ($geoPoint) {
            $search['sort'] = [
                '_geo_distance' => [
                    'post_geo_location'  => $geoPoint['area_location']
                ]
            ];
        }

        $term = 'term';
        if (isset($data['!post_like_count'])) {
            if (is_array($data['!post_like_count'])) {
                $term = 'terms';
            }

            $search['query']['bool']['must_not'][$term]['post_like_count'] = $data['!post_like_count'];
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

        // Checks for matching
        if (isset($data['matching']) && !empty($data['matching'])) {
            // Loops through the matching fields
            foreach ($data['matching'] as $matching) {
                $search['query']['bool']['filter'][]['bool']['should'][]['term']['post_position']
                    = '*' . $matching['post_position'] . '*';
                $search['query']['bool']['filter'][]['bool']['should'][]['term']['post_location']
                    = '*' . $matching['post_location'] . '*';
            }
        }

        // Checks for post_position and is array
        if (isset($data['post_position']) && is_array($data['post_position'])) {
            $data['post_position'] = array_column($data['post_position'], 'position_name');

            $search['query']['bool']['filter'][]['query_string'] = [
                'query' => implode(',', $data['post_position']),
                'fields' => ['post_position'],
                'default_operator' => 'OR'
            ];
        }

        // $search['query']['bool']['boost_mode'] = '25';

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

            $like = [];
            // Checks if there are likers
            if (isset($row['post_likes']) && !empty($row['post_likes'])) {
                // Lopps through the likers
                foreach ($row['post_likes'] as $key => $liker) {
                    $like[$liker['profile_id']] = $liker;
                }
            }
            // Checks for post_package
            if (!isset($row['post_package']) || empty($row['post_package'])) {
                $row['post_package'] = [];
            } elseif (!is_array($row['post_package'])) {
                $row['post_package'] = json_decode($row['post_package'], true);
            }

            // Checks for profile_package
            if (!isset($row['profile_package']) || empty($row['profile_package'])) {
                $row['profile_package'] = [];
            } elseif (!is_array($row['profile_package'])) {
                $row['profile_package'] = json_decode($row['profile_package'], true);
            }

            $row['post_slug'] = $this->slugify(
                $row['post_position'],
                $row['post_id']
            );

            $row['likers'] = $like;
            /* Omit line of code as it breaks special character strings
            $row['post_position'] = utf8_decode($row['post_position']);
            $row['post_location'] = utf8_decode($row['post_location']);
            $row['post_name'] = utf8_decode($row['post_name']);
            */

            // Append the row to the list to be returned
            $rows[] = $row;
        }

        // Checks if we need to get matches
        if (isset($data['withMatches'])) {
            // Loops through the rows
            foreach ($rows as $i => $row) {
                // Set the data for matches
                $matchData = array();
                $matchData['like_filter']['post_position'] = $row['post_position'];
                $matchData['like_filter']['post_location'] = $row['post_location'];
                $postType = $row['post_type'] == 'seeker' ? 'poster' : 'seeker';
                $matchData['not_expires'] = true;
                $matchData['post_active'] = 1;
                $matchData['filter']['post_type'] = $postType;
                // Gets the total number of matches
                $rows[$i]['post_total_matches'] = $this->search($matchData)['total'];
            }
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
    public function searchLocation($location)
    {
        $resource = cradle()->package('global')->service('elastic-main');
        $search['query']['bool']['filter'][]['query_string'] = [
            'query' => $location,
            'fields' => ['area_name']
        ];

        $search['sort'] = ['area_id' => 'asc'];

        try {
            $results = $resource->search([
                'index' => 'area',
                'type' => static::INDEX_TYPE,
                'body' => $search,
                'size' => 10,
                'from' => 0
            ]);
        } catch (NoNodesAvailableException $e) {
            return false;
        }

        // fix it
        $rows = array();
        foreach ($results['hits']['hits'] as $item) {
            $row = $item['_source'];

            // Checks for area_location
            if (isset($row['area_location']) && !empty($row['area_location'])) {
                // $row
            }

            // Append the row to the list to be returned
            $rows[] = $row;
        }

        //return response format
        return [
            'rows' => $rows,
            'total' => $results['hits']['total']
        ];
    }

    /**
     * Get Post Totals from database
     *
     * @param *int $id
     *
     * @return array
     */
    public function getTotals()
    {
        $totals = [];
        
        $totals['companies'] = $this->getCompanyTotals();
        
        $totals['applicants'] = $this->getApplicantsTotals();

        $post = $this->search();
        $totals['posts'] = $post['total'];
        
        // count likes
        $liked = $this->search(['!post_like_count' => '0']);
        $likes = 0;

        foreach($liked['rows'] as $v) {
            $likes = $likes + $v['post_like_count'];
        }
        
        $totals['connections'] = $likes;
        return $totals;
    }

    /**
     * Get Post Totals from database
     *
     * @param *int $id
     *
     * @return array
     */
    public function getCompanyTotals()
    {
        $data = ['!profile_company'  => '_null_'];
        $profileElastic = \Cradle\Module\Profile\Service::get('elastic');

        $results = $profileElastic->search($data);
        return $results['total'];
    }


    /**
     * Get Post Totals from database
     *
     * @param *int $id
     *
     * @return array
     */
    public function getApplicantsTotals()
    {
        $data = ['!profile_company'  => ['_empty_', '_null_']];
        $profileElastic = \Cradle\Module\Profile\Service::get('elastic');

        $results = $profileElastic->search($data);
        return $results['total'];
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
        $field = 'post_position';

        if (isset($data['fuzziness']) && is_numeric($data['fuzziness'])) {
            $fuzziness = $data['fuzziness'];
        }

        if (isset($data['prefix']) && is_numeric($data['fuzziness'])) {
            $prefix = $data['prefix'];
        }
        
        $fields = ['post_name', 'post_location', 'post_position'];
        if (isset($data['field']) && in_array($data['field'], $fields)) {
            $field = $data['field'];
        }

        if (isset($data['filter']) && !empty($data['filter'])) {
            $filter = $data['filter'];
        }

        if (isset($data['like_filter']) && is_array($data['like_filter'])) {
            $likeFilter = $data['like_filter'];
        }

        if (isset($data['range']) && is_numeric($data['range'])) {
            $range = $data['range'];
        }

        //prepare the search object
        // TODO: need to cleanup term
        $data['term'] = preg_replace('/\//', '', $data['term']);
        $search = ['query' =>
            ['match' => [
                $field => [
                    'query' => $data['term'],
                    'fuzziness' => $fuzziness,
                    'prefix_length' => $prefix
                    ]
                ]
            ]
        ];
        
        // check for filters
        if (!empty($filter)) {
            $obj = [];
            
            // post_expires filter is special :D
            if (isset($filter['post_expires'])) {
                $expires = [];
                if ($filter['post_expires'] == '-1') {
                    $expires = ['lte'   => date('Y-m-d H:i:s')];
                } else {
                    $expires = ['gte'   => date('Y-m-d H:i:s')];
                }

                $obj[] = ['range' => ['post_expires' => $expires]];
                unset($filter['post_expires']);
            }

            // Checks for post_location
            if (isset($filter['post_location'])) {
                $likeFilter['post_location'] = $filter['post_location'];
                unset($filter['post_location']);
            }

            foreach ($filter as $field => $v) {
                $obj[] = ['term' => [$field => $v]];
            }
            
            $search = [
                'query' => [
                    'bool' => [
                        'must' => $search['query'],
                        'filter' => $obj
                    ]
                ]
            ];

            // Checks if the notNullJson is not empty
            if (!empty($likeFilter)) {
                // Loops through the columns
                foreach ($likeFilter as $column => $value) {
                    // Checks if the value is an array
                    if (is_array($value)) {
                        $implode = array();

                        // Loops through the content of the value
                        foreach ($value as $v) {
                            $implode[] = '*'.$v.'*';
                        }

                        $search['query']['bool']['filter'][]['query_string'] = [
                            'query' => '('.implode(',', $implode).')',
                            'fields' => [$column],
                            'default_operator' => 'OR'
                        ];
                    } else {
                        $search['query']['bool']['filter'][]['query_string'] = [
                            'query' => '*' . $value . '*',
                            'fields' => [$column],
                            'default_operator' => 'OR'
                        ];
                    }
                }
            }
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
        
        if (!empty($rows)) {
        }

        //return response format
        return [
            'rows' => $rows,
            'total' => $results['hits']['total']
        ];
    }

    public function slugify($string, $id)
    {
        $slug = preg_replace("/[^a-zA-Z0-9_\-\s]/i", '', $string);
        $slug = str_replace(' ', '-', trim($slug));
        $slug = preg_replace("/-+/i", '-', $slug);
        $slug = strtolower($slug);
        $slug = substr($slug, 0, 90);
        $slug = str_replace('-', ' ', $slug);
        $slug = ucwords($slug);
        $slug = str_replace(' ', '-', $slug);
        $slug = $slug . '-' . 'p' . $id;

        return $slug;
    }
}
