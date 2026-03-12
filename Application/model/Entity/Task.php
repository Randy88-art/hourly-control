<?php
declare(strict_types=1);

namespace Application\model\Entity;

final readonly class Task
{
    public function __construct(
        public int $task_id,
        public string $task_name,
        public string|null $task_description,
        public int $task_priority_id,
        public int $active
    )
    {}

    public function with(array $data): self
    {
        return new self(
            $data['task_id'] ?? $this->task_id,
            $data['task_name'] ?? $this->task_name,
            $data['task_description'] ?? $this->task_description,
            $data['task_priority_id'] ?? $this->task_priority_id,
            $data['active'] ?? $this->active,
        );        
    }
}
