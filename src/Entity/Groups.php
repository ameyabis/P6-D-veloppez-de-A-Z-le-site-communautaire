<?php

namespace App\Entity;

use App\Repository\GroupsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GroupsRepository::class)]
class Groups
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'groups', targetEntity: Trick::class)]
    private Collection $trick;

    #[ORM\Column(length: 255, nullable: false)]
    private string $illustrationUrl;

    public function __construct()
    {
        $this->trick = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Trick>
     */
    public function getTrick(): Collection
    {
        return $this->trick;
    }

    public function addTrick(Trick $trick): static
    {
        if (!$this->trick->contains($trick)) {
            $this->trick->add($trick);
            $trick->setGroups($this);
        }

        return $this;
    }

    public function removeTrick(Trick $trick): static
    {
        if ($this->trick->removeElement($trick)) {
            // set the owning side to null (unless already changed)
            if ($trick->getGroups() === $this) {
                $trick->setGroups(null);
            }
        }

        return $this;
    }

    public function getIllustrationUrl(): string
    {
        return $this->illustrationUrl;
    }

    public function setIllustrationUrl(string $illustrationUrl): static
    {
        $this->illustrationUrl = $illustrationUrl;

        return $this;
    }
}
