# ComposerAPI
A wrapper for [Composer](http://getcomposer.org) to call it's commands programmatically using a simple object oriented API: turns <code>php composer.phar require monolog/monolog</code> into <code>$composer->require(array('monolog/monolog:*'));</code>.

## Installation
As always, the easiest (and the recommended) way to install is using composer:
<pre><code>
composer require kabachello/composerapi:*
</code></pre>

There is no simple way to install composer an the API without using composer itself. Theoretically you could include ComposerAPI.php in your code manually, but you would need to make sure an installation of "composer/composer" is available under the namespace "\Composer". The trouble is, however, that composer has lot's of dependencies itself, so you will probably end up needing the packaged version (composer.phar) anyway. If so, use the simple composer-install above.

## Quick start
Here is an example, that adds the monolog library to an existing composer.json manifest and installes it with all dependencies:
```php
<?php
$composer = new \kabachello\ComposerAPI\ComposerAPI("path_to_the_folder_with_your_composer_json");
$output = $composer->require(array('monolog/monolog:*);
echo ($output);
?>
```

## Supported commands
- *install*: <code>$composer->install()</code>. This will probably not be used very often because the API mostly makes sense for managing existing installations and not for installing "from scratch".
- *update*: Install: <code>$composer->update()</code> or <code>$composer->update(array('monolog/monolog', 'kabachello/composerapi'))</code>
- *require*: <code>$composer->require(array('monolog/monolog:~1.16', 'slim/slim'))</code>
- *remove*: <code>$composer->remove(array('monolog/monolog'))</code>
- *search*: <code>$composer->search(array('composerapi'))</code>
- *show*: <code>$composer->show()</code> or <code>$composer->show(array('--latest'))</code>
- *outdated*: <code>$composer->outdated()</code>
- *suggests*: <code>$composer->suggests()</code> or <code>$composer->suggests(array('symfony/event-dispatcher'), array('--tree'))</code>
- *depends*: <code>$composer->depends('doctrine/lexer', array('--tree'))</code>
- *prohibits*: <code>$composer->prohibits('symfony/symfony', '3.1', array('--tree'))</code>
- *validate*: <code>$composer->validate()</code>