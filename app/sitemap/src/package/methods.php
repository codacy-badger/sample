<?php //-->
/**
 * This file is part of the Salaaap Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Generic template method for app/wwww
 *
 * @param *string $path
 * @param array   $data
 * @param array   $partial
 *
 * @return string
 */
$cradle->package('/app/sitemap')->addMethod('template', function (
    $path,
    array $data = [],
    $partials = []
) {

    // get the root directory
    $root = __DIR__ . '/../template/';

    //render
    $handlebars = cradle('global')->handlebars();

    // check for partials
    if (!is_array($partials)) {
        $partials = [$partials];
    }

    foreach ($partials as $partial) {
        //Sample: product_comment => product/_comment
        //Sample: flash => _flash
        $file = str_replace('_', '/_', $partial) . '.xml';

        if (strpos($file, '_') === false) {
            $file = '_' . $file;
        }

        // register the partial
        $handlebars->registerPartial($partial, file_get_contents($root . $file));
    }

    // set the main template
    $template = $handlebars->compile(file_get_contents($root . $path . '.xml'));

    return $template($data);
});
