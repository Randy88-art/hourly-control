<?php
declare(strict_types=1);

namespace Application\model\Entity;

final readonly class Project
{
    public function __construct(        
        public string|null $project_name,
        public string|null $project_description,
        public int|null $active,
        public ?int $project_id = null,
    )
    {}

    public function with(array $data): self
    {
        return new self(            
            $data['project_name']        ?? $this->project_name,
            $data['project_description'] ?? $this->project_description,
            $data['active']              ?? $this->active,
            $data['project_id']          ?? $this->project_id,
        );
    }
}
