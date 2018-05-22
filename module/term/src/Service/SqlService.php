<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Term\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Term SQL Service
 *
 * @vendor   Acme
 * @package  term
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'term';

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
     * Increment hits
     *
     * @param *string $name
     * @param *string $type
     *
     * @return array
     */
    public function addHit($name, $type)
    {
        $term = [];
        if (is_string($name)) {
            $term[] = $name;
        } else {
            $term = $name;
        }

        foreach ($term as $key => $name) {
            if (!$this->exists($name, $type)) {
                return $this->create([
                    'term_name' => $name,
                    'term_type' => $type
                ]);
            }

            return $this->resource->query('UPDATE term SET term_hits = term_hits + 1 '
            . 'WHERE term_name=:bind0bind AND term_type=:bind1bind', [
                ':bind0bind' => $name,
                ':bind1bind' => $type
            ]);
        }
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
            ->setTermCreated(date('Y-m-d H:i:s'))
            ->setTermUpdated(date('Y-m-d H:i:s'))
            ->save('term')
            ->get();
    }

    /**
     * Checks to see if the slug already exists
     *
     * @param *string $name
     * @param *string $type
     *
     * @return bool
     */
    public function exists($name, $type)
    {
        return !!$this->resource
            ->search('term')
            ->filterByTermName($name)
            ->filterByTermType($type)
            ->getRow();
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
        $search = $this->resource->search('term');

        $search->filterByTermId($id);

        $results = $search->getRow();

        if(!$results) {
            return $results;
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
            ->setTermId($id)
            ->remove('term');
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

        if (isset($data['export'])) {
            $range = 0;
        }

        if (isset($data['q'])) {
            $keywords = $data['q'];

            if(!is_array($keywords)) {
                $keywords = [$keywords];
            }
        }

        if (!isset($filter['term_active'])) {
            $filter['term_active'] = 1;
        }

        // Checks if active is set to -1
        if ($filter['term_active'] == -1) {
            unset($filter['term_active']);
        }

        $search = $this->resource
            ->search('term')
            ->setStart($start)
            ->setRange($range);

        //add filters
        foreach ($filter as $column => $value) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' = %s', $value);
            }
        }

        //keyword?
        if (isset($keywords)) {
            foreach ($keywords as $keyword) {
                $or = [];
                $where = [];
                $where[] = 'LOWER(term_name) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                array_unshift($or, '(' . implode(' OR ', $where) . ')');

                call_user_func([$search, 'addFilter'], ...$or);
            }
        }

        // Checks for omit url from term name
        if (isset($data['not_url'])) {
            // Lets omit urls from the terms
            $search->addFilter('term_name NOT LIKE "%www%"');
        }

        //add sorting
        foreach ($order as $sort => $direction) {
            // Default encasement
            $encase = 'TRIM(LOWER(%s))';

            // Checks if we should not encase the sorting
            switch ($sort) {
                case (strpos($sort, '_id') !== false) :
                    break;

                case (strpos($sort, '_hits') !== false) :
                    break;

                default :
                    $sort = sprintf($encase, $sort);
                    break;
            }

            $search->addSort($sort, $direction);
        }


        $rows = $search->getRows();

        foreach($rows as $i => $results) {

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
            ->setTermUpdated(date('Y-m-d H:i:s'))
            ->save('term')
            ->get();
    }
}
