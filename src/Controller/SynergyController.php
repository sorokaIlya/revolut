<?php

namespace App\Controller;

use App\DTO\CommentDto;
use App\DTO\PostDto;
use App\Entity\Comments;
use App\Entity\Post;
use App\Entity\Tag;
use App\Entity\User;
use App\Repository\CommentsRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class SynergyController extends AbstractController
{
    /**
     * @Route("/api/my/posts", name="getMyPosts",methods={"GET"})
     */
    public function getMyPosts(PostRepository $postRepository):JsonResponse
    {
        //for register user method
        $user=$this->getUser();
        $posts=$postRepository->findBy(['creator'=>$user->getId()]);
        //$json=$serializer->serialize($posts,'json',['groups'=>'post:read']);
        return $this->json($posts,200,[],['groups=>show:post_view']);
    }

    /**
     * @Route("/api/post", name="addPost",methods={"POST"})
     */
    public function addPost(Request $request,SerializerInterface $serializer)
    {
        $em=$this->getDoctrine()->getManager();
        $content = $request->getContent();
        if(!$content){
            return new JsonResponse(['msg'=>'error'],Response::HTTP_BAD_REQUEST);
        }
        /** @var User $user */
        $user=$this->getUser();
        /** @var  PostDto $postDto */
        $postDto = $serializer->deserialize($content,PostDto::class,'json');

        $tagName=$em->getRepository(Tag::class)->findOneBy(['name'=>$postDto->tag]);
        /** @var  Tag $tagName */
        $post=Post::createPostFromApi($postDto,$user,$tagName);


        $em->persist($post);
        $em->flush();
        return $this->json(['mes'=>'creates success'],201);
    }

    /**
     * @Route("/api/rm_post/{id}", name="removePost",methods={"POST"})
     */
        public function deletePost($id,PostRepository $postRepository,EntityManagerInterface $em){
            $user = $this->getUser();
            $post=$postRepository->findOneBy(['creator'=>$user->getId()]);
            if(!$post){
                return new JsonResponse(['msg'=>'this user is not allowed delete this post'],Response::HTTP_NOT_FOUND);
            }
            $post->deletedHimselfByApi($user);
            $em->remove($post);
            $em->flush();
            return $this->json([
                'message' => "Post with ID $id has been deleted"
            ]);
        }


    /**
     * @Route("/api/up_post/{id}", name="updatePosts",methods={"PUT"})
     */
    public function updatePosts($id,EntityManagerInterface $em,Request $request,SerializerInterface $serializer)
    {
        $content = json_decode($request->getContent(), true);

        $post = $em->getRepository(Post::class)->findOneBy(["id" => $id]);
        if (!$post) {
            return new JsonResponse(['message' => 'not found'], Response::HTTP_NOT_FOUND);
        }

        /** @var  Post $post */
        $post($content['text']);
        $em->flush();
        return $this->json($post, 200, [], ['groups' => 'post:read']);
    }

    /**
     * @Route("/api/add_comment/{id}", name="add_comment",methods={"POST"})
     * @param $id
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function addComment($id,Request $request,EntityManagerInterface $em,SerializerInterface $serializer){
        /** @var User $user */
        $user=$this->getUser();
        /** @var Post $post */
        $post=$em->getRepository(Post::class)->findOneBy(['id'=>$id]);
        /** @var CommentDto $commentDto */
        $commentDto=$serializer->deserialize($request->getContent(),CommentDto::class,'json');
        $comment = Comments::addCommentFromApi($commentDto,$post,$user);

        $em->persist($comment);
        $em->flush();
        return $this->json($comment,201,[],['groups'=>'post:read']);
    }

    /**
     * @Route("/api/like/{id}", name="like_post",methods={"POST"})
     */
    public function Like(Post $post,EntityManagerInterface $em)
    {
        $likes=$post->addLike($this->getUser());
        $em->flush();
        return $this->json(['count'=>$likes],200,[],['groups'=>'post:read']);
    }

    /**
     * @Route("/api/rm_comment/{id}", name="rm_comment",methods={"POST"})
     */
    public function deltComment($id,CommentsRepository $cm)
    {
      $comment=$cm->findOneBy(['id'=>$id]);
        if(!$comment){
            return new JsonResponse(['msg'=>'not found'],Response::HTTP_NOT_FOUND);
        }
        $em=$this->getDoctrine()->getManager();

        $comment->deleteCommentFromApi($comment->getPost(),$comment->getOwner());
        $em->remove($comment);
        $em->flush();
        return $this->json(['msg'=>"deleted!"],200);
    }

    /**
     * @Route("/all/delete/{id}", name="deleteUser",methods={"POST"})
     */
    public function deleteUser(User $user,EntityManagerInterface $em):JsonResponse
    {
        $user->deleteOther();
        $em->remove($user);
        $em->flush();
        return $this->json(['msg'=>'clear!'],200,[]);
    }

    /**
     * @Route("/all/posts", name="all-posts-for-all",methods={"GET"})
     */
    public function postAll(SerializerInterface $serializer):JsonResponse
    {
        //all posts for show page but i am being needfull in sort
        $em=$this->getDoctrine()->getManager();
        // my own method in PostRepository
        $posts = $em->getRepository(Post::class)->getAllPostsSortedByDescCount();
        //$postedSer =$serializer->deserialize($posts,'json',['groups=>show:post_view']);
        return $this->json($posts,200,[],['groups=>show:post_view']);
    }
    /**
     * @Route("/all/tags", name="tags_recieve",methods={"GET"})
     */
    public function tagsAll(EntityManagerInterface $em):JsonResponse
    {
        $tags=$em->getRepository(Tag::class)->findAll();
        return $this->json($tags,200,[],['groups'=>'post:read']);
    }
}
