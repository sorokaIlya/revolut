<?php

namespace App\Entity;

use App\DTO\CommentDto;
use App\Repository\CommentsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=CommentsRepository::class)
 */
class Comments
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
    private $review;

    /**
     * @ORM\ManyToOne(targetEntity=Post::class, inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $post;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    
    private function __construct(string $id,string $review, Post $post,User $owner)
    {
        $this->id=$id;
        $this->review=$review;
        $this->post=$post;
        $this->post->addComment($this);
        $this->owner = $owner;
        $this->owner->addComment($this);
    }
    public static function addCommentFromApi(CommentDto $commentDto,Post $post,User $owner):self
    {
        return new static($commentDto->id,$commentDto->review,$post,$owner);
    }

    public  function deleteCommentFromApi(Post $post,User $owner):self
    {
        $owner->removeComment($this);
        $post->removeComment($this);
        if($this->owner ===$owner){
            $this->owner=null;
        }
        if($this->post ===$post){
            $this->post=null;
        }
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getReview(): ?string
    {
        return $this->review;
    }


    public function getPost(): ?Post
    {
        return $this->post;
    }


    public function getOwner(): ?User
    {
        return $this->owner;
    }

}
