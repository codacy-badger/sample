<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Tracking\Form\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Form SQL Service
 *
 * @vendor   Acme
 * @package  form
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'form';

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
            ->setFormCreated(date('Y-m-d H:i:s'))
            ->setFormUpdated(date('Y-m-d H:i:s'))
            ->save('form')
            ->get();
    }

    /**
     * Get detail from database
     *
     * @param *int $id
     *
     * @return array
     */
    public function get($id, $active = null)
    {
        $search = $this->resource->search('form');
        $search->filterByFormId($id);

        // Checks if an active value was passed
        if (!is_null($active)) {
            $search->filterByFormActive($active);
        }

        $results = $search->getRow();
        return $results;
    }

    /**
     * Get detail from database
     *
     * @param *int $post
     *
     * @return array
     */
    public function getPostForm($post, $active = null)
    {
        $search = $this->resource->search('form');
        $search->innerJoinUsing('post_form', 'form_id')
            ->filterByPostId($post);

        // Checks for form_active
        if ($active) {
            $search->filterByFormActive($active);
        }

        $results = $search->getRow();
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
            ->setFormId($id)
            ->remove('form');
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
        $exactFilter = [];
        $notFilter = [];
        $range = 50;
        $start = 0;
        $order = [];
        $count = 0;
        $keywords = null;

        if (isset($data['filter']) && is_array($data['filter'])) {
            $filter = $data['filter'];
        }

        if (isset($data['exact_filter']) && is_array($data['exact_filter'])) {
            $exactFilter = $data['exact_filter'];
        }

        if (isset($data['not_filter']) && is_array($data['not_filter'])) {
            $notFilter = $data['not_filter'];
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

            if (!is_array($keywords)) {
                $keywords = [$keywords];
            }
        }

        if (!isset($filter['form_active'])) {
            $filter['form_active'] = 1;
        }

        $search = $this->resource
            ->search('form')
            ->setStart($start)
            ->setRange($range);

        //join profile
        $search->innerJoinUsing('profile_form', 'form_id');
        $search->innerJoinUsing('profile', 'profile_id');

        // Checks if there are filters
        if (!empty($filter)) {
            foreach ($filter as $column => $value) {
                if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                    $search->addFilter($column . ' = %s', $value);
                }
            }
        }

        // Checks if there are exact filters
        if (!empty($exactFilter)) {
            // Loops through the filters
            foreach ($exactFilter as $column => $value) {
                // Checks if the value is not empty
                if (!is_null($value)) {
                    if (is_numeric($value)) {
                        $search->addFilter($column . ' = '. $value);
                    } else {
                        $search->addFilter($column . ' = "' . $value . '"');
                    }
                }
            }
        }

        // Checks if the notFilter is not empty
        if (!empty($notFilter)) {
            // Loops through the reverse filter
            foreach ($notFilter as $column => $value) {
                // Checks if the value is an array
                if (is_array($value)) {
                    // Loops through the array
                    foreach ($value as $v) {
                        // Checks if the value being filtered out is null
                        if (empty($v)) {
                            $search->addFilter($column . ' IS NOT NULL');
                        } else {
                            $search->addFilter($column . ' != ' . $v);
                        }
                    }
                } else {
                    // Checks if the value being filtered out is null
                    if (empty($value)) {
                        $search->addFilter($column . ' IS NOT NULL');
                    } else {
                        $search->addFilter($column . ' != ' . $value);
                    }
                }
            }
        }

        //keyword?
        if (isset($keywords)) {
            foreach ($keywords as $keyword) {
                $or = [];
                $where = [];

                $where[] = 'LOWER(form_name) LIKE %s';
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
            ->setFormUpdated(date('Y-m-d H:i:s'))
            ->save('form')
            ->get();
    }

    /**
     * Links question
     *
     * @param *int $formPrimary
     * @param *int $questionPrimary
     */
    public function linkQuestion($formPrimary, $questionPrimary)
    {
        return $this->resource
            ->model()
            ->setFormId($formPrimary)
            ->setQuestionId($questionPrimary)
            ->insert('form_question');
    }

    /**
     * Unlinks question
     *
     * @param *int $formPrimary
     * @param *int $questionPrimary
     */
    public function unlinkQuestion($formPrimary, $questionPrimary)
    {
        return $this->resource
            ->model()
            ->setFormId($formPrimary)
            ->setQuestionId($questionPrimary)
            ->remove('form_question');
    }

    /**
     * Unlinks All question
     *
     * @param *int $formPrimary
     * @param *int $questionPrimary
     */
    public function unlinkAllQuestion($formPrimary)
    {
        return $this->resource
            ->model()
            ->setFormId($formPrimary)
            ->remove('form_question');
    }

}
