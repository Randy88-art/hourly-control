<?php

declare(strict_types=1);

namespace Application\model;

final class Task
{
    private ?int    $task_id   = null;
    private ?string $task_name = null;
    private ?int    $active    = null;

    public function __construct(
        private array $fields = []
    )
    {
        $this->setEntity($fields);
    }

    public function setEntity(array $fields) : self
    {
        if(!empty($fields)) {
            foreach($fields as $key => $value) {
                $method = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
                
                if(method_exists($this, $method)) {
                    $this->$method($value);
                }
            }
        }

        return $this;
    }

    // Define getters and setters
    public function setTaskId(int $id): self
    {
        $this->task_id = $id;

        return $this;
    }

    public function getTaskId(): int
    {
        return $this->task_id;
    }

    public function setTaskName(string $name): self
    {
        $this->task_name = $name;

        return $this;
    }

    public function getTaskName(): string
    {
        return $this->task_name;
    }

    public function setActive(int $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getActive(): int
    {
        return $this->active;
    }
}
