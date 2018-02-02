<?php

// Require the template object
require_once __DIR__ . '/../src/TemplateException.php';
require_once __DIR__ . '/../src/TemplateInterface.php';
require_once __DIR__ . '/../src/Template.php';

// Add use
use gdwebs\template\Template;

// Create a new instance
$template = new Template(__DIR__ . '/example/template.html');

// Set the language and title of the template.html
$template
    ->setVariable('title', 'Page Title')
    ->setVariable('lang', 'en');

// Get the stylesheet sub template
$stylesheet = $template->getSubTemplate('stylesheet');

// Replace href variable within stylesheet template
$stylesheet->setVariable('href', 'path-to-stylesheet.css');

// Convert stylesheet template back to a string and add it to main template head variable
$template->setVariable('head', $stylesheet->render());

// Now we also add a javascript file and also add this to the head
$javascript = $template->getSubTemplate('javascript');
$javascript->setVariable('src', 'path-to-javascript.js');
$template->setVariable('head', $javascript->render());

// Below is also an example with sub templates having more templates (recursive)
// For this see the body part of template.html
// We render this and add it to the body of the template.html file
$table = $template->getSubTemplate('table');
$rows  = '';

// Create 10 times: <tr>
for ($i = 0; $i < 10; $i++) {
    $row = $table->getSubTemplate('row');
    // Note: In template.html there are 2 cell variables, both will be replaced.
    $row->setVariable('cell', 'Contents of cell');
    $row->setVariable('cell2', 'Contents of cell 2');
    $rows .= $row->render();
}

// Add all generated rows to the table
$table->setVariable('rows', $rows);

// Add the table to the body
$template->setVariable('body', $table->render());

// Outputs the generated HTML (or see output.html for the generated output).
echo $template->render();
