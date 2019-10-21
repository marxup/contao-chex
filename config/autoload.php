<?php
/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
    'marxup\chex'
));
/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
    'marxup\chex\classes\Chex' => "system/modules/marxup_chex/classes/Chex.php"
));
/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
    'chex' => "system/modules/marxup_chex/templates",
));