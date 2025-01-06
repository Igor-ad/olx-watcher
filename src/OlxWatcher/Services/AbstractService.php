<?php declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Services;

abstract class AbstractService extends BaseService
{
    protected array $subjectKeys;
    protected array $updatedKeys = [];

    public function __construct()
    {
        parent::__construct();
        $this->subjectKeys = $this->cache->keys();
    }
}
