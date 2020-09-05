<?php

namespace App\Entity;

use App\DTO\CreateUserDto;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(type="string")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity=Comments::class, mappedBy="owner")
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity=Post::class, mappedBy="creator")
     */
    private $posts;

    private function __construct(string $email, string $role, string $id)
    {
        $this->id = $id;
        $this->email = $email;
        $this->roles = [$role];
        $this->comments = new ArrayCollection();
        $this->posts = new ArrayCollection();
    }


    public static function createUserFromRegistration(CreateUserDto $dto, UserPasswordEncoderInterface $passwordEncoder): self {
        $user = new static($dto->email, 'ROLE_USER',$dto->id);
        $user->password = $passwordEncoder->encodePassword($user, $dto->password);
        return $user;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|Comments[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comments $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
        }
        return $this;
    }

    function  deleteOther(EntityManagerInterface $em){
        //$this->getComments()->clear();
        //$this->getPosts()->clear();
        foreach ($this->getComments() as $comment) {
            $em->remove($comment);
        }
        foreach ($this->getPosts() as $post) {
            $em->remove($post);
        }
    }

    public function removeComment(Comments $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
          //  if ($comment->getOwner() === $this) {
               // $comment->setOwner(null);
           // }
        }
        return $this;
    }

    /**
     * @return Collection|Post[]
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
          //  $post->setCreator($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->contains($post)) {
            $this->posts->removeElement($post);
            // set the owning side to null (unless already changed)
           // if ($post->getCreator() === $this) {
                //$post->setCreator(null);
            //}
        }

        return $this;
    }


}
