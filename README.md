[![Packagist](https://img.shields.io/packagist/l/gdwebs/template.svg?maxAge=86400)](LICENSE.md)
[![GitHub release](https://img.shields.io/github/release/GeoffreyDijkstra/template.svg?maxAge=86400)](https://github.com/GeoffreyDijkstra/template/releases)
[![Packagist](https://img.shields.io/packagist/dd/gdwebs/template.svg?maxAge=86400)](https://packagist.org/packages/gdwebs/template)

# Template
A template system which can be used with any file type.
It's created in a way that will force you to keep your template and code seperated.
See the example below for a demo.

##Installation
This package can be installed through composer by requiring `gdwebs\template`.

##Issues + Pull Requests
For issues use the "Issues" tab or even better send a pull request to solve the issue ;)
Note that the code should follow PSR2 standards.

# Example
Using: [template.html](example/template.html).

```php
<?php

// Require the template object
require_once __DIR__ . '/src/TemplateException.php';
require_once __DIR__ . '/src/TemplateInterface.php';
require_once __DIR__ . '/src/Template.php';

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

// Output the generated HTML
echo $template->render();
```
See [output.html](example/output.html) for the generated output of this code.
Or copy this code to a `.php` file and watch the output in your browser.
