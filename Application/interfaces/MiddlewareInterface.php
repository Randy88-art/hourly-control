<?php
declare(strict_types=1);

namespace Application\interfaces;

interface MiddlewareInterface
{
    public function handle(): void;
}