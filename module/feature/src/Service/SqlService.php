<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Feature\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Feature SQL Service
 *
 * @vendor   Acme
 * @package  feature
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'feature';

    /**
     * Registers the resource for use
     *
     * @param Resource $resource
     */
    public function __construct(Resource $resource)
    {
        $this->resource = SqlFactory::load($resource);
    }

    /**
     * Create in database
     *
     * @param array $data
     *
     * @return array
     */
    public function create(array $data)
    {
        return $this->resource
            ->model($data)
            ->setFeatureCreated(date('Y-m-d H:i:s'))
            ->setFeatureUpdated(date('Y-m-d H:i:s'))
            ->save('feature')
            ->get();
    }

    /**
     * Get detail from database
     *
     * @param *int $id
     *
     * @return array
     */
    public function get($id)
    {
        $search = $this->resource->search('feature');

        $search->filterByFeatureId($id);

        $results = $search->getRow();

        if(!$results) {
            return $results;
        }

        if($results['feature_keywords']) {
            $results['feature_keywords'] = json_decode($results['feature_keywords'], true);
        } else {
            $results['feature_keywords'] = [];
        }

        if($results['feature_links']) {
            $results['feature_links'] = json_decode($results['feature_links'], true);
        } else {
            $results['feature_links'] = [];
        }

        if ($results['feature_meta']) {
            $results['feature_meta'] = json_decode($results['feature_meta'], true);
        } else {
            $results['feature_meta'] = [];
        }

        return $results;
    }

    /**
     * Get detail from database
     *
     * @param *int $id
     *
     * @return array
     */
    public function getSlug($slug)
    {

        $search = $this->resource->search('feature');

        $search->filterByFeatureSlug($slug)
            ->filterByFeatureActive(1);

        $results = $search->getRow();

        if(!$results) {
            return $results;
        }

        if($results['feature_keywords']) {
            $results['feature_keywords'] = json_decode($results['feature_keywords'], true);
        } else {
            $results['feature_keywords'] = [];
        }

        if($results['feature_links']) {
            $results['feature_links'] = json_decode($results['feature_links'], true);
        } else {
            $results['feature_links'] = [];
        }

        if ($results['feature_meta']) {
            $results['feature_meta'] = json_decode($results['feature_meta'], true);
        } else {
            $results['feature_meta'] = [];
        }

        return $results;
    }

    /**
     * Remove from database
     * PLEASE BECAREFUL USING THIS !!!
     * It's here for clean up scripts
     *
     * @param *int $id
     */
    public function remove($id)
    {
        //please rely on SQL CASCADING ON DELETE
        return $this->resource
            ->model()
            ->setFeatureId($id)
            ->remove('feature');
    }

    /**
     * Search in database
     *
     * @param array $data
     *
     * @return array
     */
    public function search(array $data = [])
    {
        $filter = [];
        $range = 50;
        $start = 0;
        $order = [];
        $count = 0;

        $keywords = null;

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
        if (!isset($filter['feature_active'])) {
            $filter['feature_active'] = 1;
        }
        if ($filter['feature_active'] == -1) {
            unset($filter['feature_active']);
        }

        $search = $this->resource
            ->search('feature')
            ->setStart($start)
            ->setRange($range);



        //add filters
        foreach ($filter as $column => $value) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' = %s', $value);
            }
        }

        // Checks for keywords
        if (isset($keywords)) {
            $or = [];
            $where = [];

            // Loops through the keywords
            foreach ($keywords as $keyword) {
                $where[] = 'LOWER(feature_title) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
            }


            array_unshift($or, '(' . implode(' OR ', $where) . ')');
            call_user_func([$search, 'addFilter'], ...$or);
        }
        // Loops through the sorting
        foreach ($order as $sort => $direction) {
            // Adds sorting
            $sort = ''.$sort.'';
            $search->addSort($sort, $direction);
        }

        $rows = $search->getRows();

        foreach($rows as $i => $results) {

            if ($results['feature_keywords']) {
                $rows[$i]['feature_keywords'] = json_decode($results['feature_keywords'], true);
            } else {
                $rows[$i]['feature_keywords'] = [];
            }

            if ($results['feature_links']) {
                $rows[$i]['feature_links'] = json_decode($results['feature_links'], true);
            } else {
                $rows[$i]['feature_links'] = [];
            }

            if ($results['feature_meta']) {
                $rows[$i]['feature_meta'] = json_decode($results['feature_meta'], true);
            } else {
                $rows[$i]['feature_meta'] = [];
            }

        }

        //return response format
        return [
            'rows' => $rows,
            'total' => $search->getTotal()
        ];
    }

    /**
     * Update to database
     *
     * @param array $data
     *
     * @return array
     */
    public function update(array $data)
    {
        return $this->resource
            ->model($data)
            ->setFeatureUpdated(date('Y-m-d H:i:s'))
            ->save('feature')
            ->get();
    }
}
