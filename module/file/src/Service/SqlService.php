<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\File\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * File SQL Service
 *
 * @vendor   Acme
 * @package  file
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'file';

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
            ->setFileCreated(date('Y-m-d H:i:s'))
            ->setFileUpdated(date('Y-m-d H:i:s'))
            ->save('file')
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
        $search = $this->resource->search('file');

        $search->innerJoinUsing('file_comment', 'file_id');
        $search->innerJoinUsing('comment', 'comment_id');

        $search->filterByFileId($id);

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
            ->setFileId($id)
            ->remove('file');
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


        if (isset($data['q'])) {
            $keywords = $data['q'];

            if(!is_array($keywords)) {
                $keywords = [$keywords];
            }
        }

        if (!isset($filter['file_active'])) {
            $filter['file_active'] = 1;
        }

        // Unset the file_active
        if ($filter['file_active'] == -1) {
            unset($filter['file_active']);
        }

        $search = $this->resource
            ->search('file')
            ->setStart($start)
            ->setRange($range);

        //join comment
        $search->innerJoinUsing('file_comment', 'file_id');
        $search->innerJoinUsing('comment', 'comment_id');

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
                $where[] = 'LOWER(file_link) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(file_type) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                array_unshift($or, '(' . implode(' OR ', $where) . ')');

                call_user_func([$search, 'addFilter'], ...$or);
            }
        }


        //add sorting
        foreach ($order as $sort => $direction) {
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
            ->setFileUpdated(date('Y-m-d H:i:s'))
            ->save('file')
            ->get();
    }

    /**
     * Links comment
     *
     * @param *int $filePrimary
     * @param *int $commentPrimary
     */
    public function linkComment($filePrimary, $commentPrimary)
    {
        return $this->resource
            ->model()
            ->setFileId($filePrimary)
            ->setCommentId($commentPrimary)
            ->insert('file_comment');
    }

    /**
     * Unlinks comment
     *
     * @param *int $filePrimary
     * @param *int $commentPrimary
     */
    public function unlinkComment($filePrimary, $commentPrimary)
    {
        return $this->resource
            ->model()
            ->setFileId($filePrimary)
            ->setCommentId($commentPrimary)
            ->remove('file_comment');
    }

}
