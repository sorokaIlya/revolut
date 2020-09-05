<?php

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=TagRepository::class)
 */
class Tag
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(type="string")
     * @Groups("post:read")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("post:read")
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=Post::class, mappedBy="tag")
     */
    private $related;

    public function __construct()
    {
        $this->related = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Post[]
     */
    public function getRelated(): Collection
    {
        return $this->related;
    }

    public function addRelated(Post $related): self
    {
        if (!$this->related->contains($related)) {
            $this->related[] = $related;
           // $related->setTag($this);
        }

        return $this;
    }

    public function removeRelated(Post $related): self
    {
        if ($this->related->contains($related)) {
            $this->related->removeElement($related);
            // set the owning side to null (unless already changed)
           // if ($related->getTag() === $this) {
           //     $related->setTag(null);
            //}
        }
        return $this;
    }
}
