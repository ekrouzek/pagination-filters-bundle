<?php declare(strict_types=1);

namespace Ekrouzek\PaginationFiltersBundle\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Course
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $created;

    #[ORM\Column(type: 'boolean')]
    private bool $active;

    public function __construct(string $name, \DateTime $created, bool $active)
    {
        $this->name = $name;
        $this->created = $created;
        $this->active = $active;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    public function isActive(): bool
    {
        return $this->active;
    }
}
