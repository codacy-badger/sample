<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Tracking\Question\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Question SQL Service
 *
 * @vendor   Acme
 * @package  question
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'question';

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
            ->setQuestionCreated(date('Y-m-d H:i:s'))
            ->setQuestionUpdated(date('Y-m-d H:i:s'))
            ->save('question')
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
        $search = $this->resource->search('question');
        $search->filterByQuestionId($id)
            ->innerJoinUsing('form_question', 'question_id')
            ->innerJoinUsing('form', 'form_id');

        $results = $search->getRow();

        // Checks if there was a result returned
        if (!$results) {
            return $results;
        }

        // Checks if there are question_choices
        if ($results['question_choices']) {
            $results['question_choices'] = json_decode($results['question_choices'], true);
        } else {
            $results['question_choices'] = [];
        }

        // Explodes the question_type
        $results['question_type'] = explode(',', $results['question_type']);

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
            ->setQuestionId($id)
            ->remove('question');
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

        if (!isset($filter['question_active'])) {
            $filter['question_active'] = 1;
        }

        $search = $this->resource
            ->search('question')
            ->setStart($start)
            ->setRange($range);

        $search->innerJoinUsing('form_question', 'question_id');
        $search->innerJoinUsing('form', 'form_id');

        //add filters
        foreach ($filter as $column => $value) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' = %s', $value);
            }
        }

        //add sorting
        foreach ($order as $sort => $direction) {
            $search->addSort($sort, $direction);
        }

        $rows = $search->getRows();

        foreach ($rows as $i => $results) {
            // Checks for question_choices
            if ($results['question_choices']) {
                $rows[$i]['question_choices'] = json_decode($results['question_choices'], true);
            } else {
                $rows[$i]['question_choices'] = [];
            }

            // Explodes the question_type
            $rows[$i]['question_type'] = explode(',', $results['question_type']);
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
            ->setQuestionUpdated(date('Y-m-d H:i:s'))
            ->save('question')
            ->get();
    }

    /**
     * Search in database
     *
     * @param array $data
     *
     * @return array
     */
    public function viewQuestion(array $data = [])
    {
        // get applicant by post id
        $applicant = $this->resource
            ->search('applicant')
            ->innerJoinUsing('applicant_profile', 'applicant_id')
            ->innerJoinUsing('profile', 'profile_id')
            ->innerJoinUsing('applicant_post', 'applicant_id')
            ->innerJoinUsing('post', 'post_id')
            ->innerJoinUsing('post_form', 'post_id')
            ->innerJoinUsing('form', 'form_id')
            ->filterByPostId($data['post_id'])
            ->filterByProfileId($data['profile_id'])
            ->getRow();

        $rows = [];
        $total = 0;
        if ($applicant) {
            // get question
            $questions = $this->resource
                ->search('applicant')
                ->innerJoinUsing('applicant_form', 'applicant_id')
                ->innerJoinUsing('form', 'form_id')
                ->innerJoinUsing('form_question', 'form_id')
                ->innerJoinUsing('question', 'question_id')
                ->filterByApplicantId($applicant['applicant_id']);

            //add sorting
            $questions->addSort('question_priority', 'ASC');

            $rows = $questions->getRows();

            if ($rows) {
                foreach ($rows as $r => $row) {
                    if ($row['question_choices']) {
                        $rows[$r]['question_choices'] = json_decode($row['question_choices'], true);
                    } else {
                        $rows[$r]['question_choices'] = [];
                    }
                }
            }

            $total = $questions->getTotal();
        }

        //return response format
        return [
            'rows' => $rows,
            'total' => $total
        ];
    }

    /**
     * Links answer
     *
     * @param *int $questionPrimary
     * @param *int $answerPrimary
     */
    public function linkAnswer($questionPrimary, $answerPrimary)
    {
        return $this->resource
            ->model()
            ->setQuestionId($questionPrimary)
            ->setAnswerId($answerPrimary)
            ->insert('question_answer');
    }

    /**
     * Unlinks answer
     *
     * @param *int $questionPrimary
     * @param *int $answerPrimary
     */
    public function unlinkAnswer($questionPrimary, $answerPrimary)
    {
        return $this->resource
            ->model()
            ->setQuestionId($questionPrimary)
            ->setAnswerId($answerPrimary)
            ->remove('question_answer');
    }

    /**
     * Unlinks All answer
     *
     * @param *int $questionPrimary
     * @param *int $answerPrimary
     */
    public function unlinkAllAnswer($questionPrimary)
    {
        return $this->resource
            ->model()
            ->setQuestionId($questionPrimary)
            ->remove('question_answer');
    }

}
