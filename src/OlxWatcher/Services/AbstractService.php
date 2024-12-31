<?php declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Services;

abstract class AbstractService extends BaseService
{
    protected array $subjectCollect;
    protected array $subjectKeys;
    protected array $updatedKeys;

    public function __construct()
    {
        parent::__construct();
        $this->subjectKeys = $this->cache->keys('http*');
        $this->setSubjectCollect($this->subjectKeys);
    }

    protected function setSubjectCollect(array $keys): void
    {
        $this->subjectCollect = $this->cache->mGet($keys);
    }
}
