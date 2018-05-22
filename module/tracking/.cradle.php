<?php //-->
include_once __DIR__ . '/src/Answer/events.php';
include_once __DIR__ . '/src/Applicant/events.php';
include_once __DIR__ . '/src/Form/events.php';
include_once __DIR__ . '/src/Label/events.php';
include_once __DIR__ . '/src/Question/events.php';

use Cradle\Module\Tracking\Answer\Service as AnswerService;
use Cradle\Module\Tracking\Applicant\Service as ApplicantService;
use Cradle\Module\Tracking\Form\Service as FormService;
use Cradle\Module\Tracking\Label\Service as LabelService;
use Cradle\Module\Tracking\Question\Service as QuestionService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('answer', AnswerService::class);
ServiceFactory::register('applicant', ApplicantService::class);
ServiceFactory::register('form', FormService::class);
ServiceFactory::register('label', LabelService::class);
ServiceFactory::register('question', QuestionService::class);
