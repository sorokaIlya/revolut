<?php

namespace App\Entity;

use App\DTO\PostDto;
use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity(repositoryClass=PostRepository::class)
 */
class Post
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(type="string")
     * @Groups("show:post_view")
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups("show:post_view")
     */
    private $text;

    /**
     * @ORM\OneToMany(targetEntity=Comments::class, mappedBy="post")
     */
    private $comments;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $likes = [];


    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="posts")
     * @MaxDepth(1)
     */
    private $creator;

    /**
     * @ORM\Column(type="integer")
     * @Groups("show:post_view")
     */
    private $count;

    /**
     * @ORM\ManyToOne(targetEntity=Tag::class, inversedBy="related")
     */
    private $tag;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("show:post_view")
     */
    private $title;

    public function __invoke(string $newtext)
    {
        $this->text=$newtext;
    }


    private function __construct(string $id,string $text,User $user,Tag $tagName,string $title)
    {
        $this->comments = new ArrayCollection();
        $this->tag=$tagName;
        $this->tag->addRelated($this);
        $this->count=count($this->likes);
        $this->id=$id;
        $this->text=$text;
        $this->creator=$user;
        $user->addPost($this);
        $this->title=$title;
    }

    public static function createPostFromApi(PostDto $postDto,User $user,Tag $tagName):self
    {
        /** @var  $newPost */
        $newPost = new static ($postDto->id,$postDto->text,$user,$tagName,$postDto->title);
        return $newPost;
    }
    public function deletedHimselfByApi(User $user,Tag $tag):self
    {
        $user->removePost($this);
        $this->tag->removeRelated($this);
        if($this->creator===$user){
            $this->creator=null;
        }
        if($this->tag===$tag){
            $this->tag=null;
        }
        return $this;
    }
    public function addComment(Comments $comment):self{
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
        }

        return $this;
    }
    public function removeComment(Comments $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
        }
        return $this;
    }
    public function addLike(User $user):int
    {
        if (!in_array($user->getId(),$this->likes)){
            array_push($this->likes,$user->getId());
        }else {
            $abbr_array = \array_diff($this->getLikes(), [$user->getId()]);
            $this->likes = $abbr_array;
        }
        $this->count=count($this->likes);
        return $this->count;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }


    /**
     * @return Collection|Comments[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function getLikes(): ?array
    {
        return $this->likes;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }



    public function getCount(): ?int
    {
        return $this->count;
    }

    public function getTag(): ?Tag
    {
        return $this->tag;
    }



    public function getTitle(): ?string
    {
        return $this->title;
    }




}
