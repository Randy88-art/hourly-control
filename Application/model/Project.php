<?php

declare(strict_types=1);

namespace Application\model;

final class Project
{
    private ?int    $project_id   = null;
    private ?string $project_name = null;
    private ?int    $active       = null;

    public function __construct(
        private array $fields = []
    )
    {
        $this->setProject($fields);
    }

    public function setProject(array $fields) : self
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

    public function setProjectId(int $id) : self
    {
        $this->project_id = $id;

        return $this;
    }

    public function getProjectId(): int
    {
        return $this->project_id;
    }    

    public function setProjectName(string $name): self
    {
        $this->project_name = $name;
        
        return $this;
    }

    public function getProjectName(): string
    {
        return $this->project_name;
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
