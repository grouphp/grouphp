<?php declare(strict_types=1);

use App\Kernel;
use Webmozart\Assert\Assert;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    $env = $context['APP_ENV'];
    Assert::string($env);
    return new Kernel($env, (bool) $context['APP_DEBUG']);
};
