<?php

namespace App\Controller;

use App\DTO\CreateUserDto;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class AuthController extends AbstractController
{
    /**
     * @Route("/auth/register", name="register", methods={"POST"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @param SerializerInterface $serializer
     * @param NormalizerInterface $normalizer
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function register(Request $request,UserPasswordEncoderInterface $encoder,SerializerInterface $serializer,EntityManagerInterface $em)
    {
        $content = $request->getContent();
        /** @var CreateUserDto $userDTO */
        $userDTO=$serializer->deserialize($content,CreateUserDto::class,'json');
        $user = User::createUserFromRegistration($userDTO, $encoder);

        $em->persist($user);
        $em->flush();
        return $this->json([$user],201,[],['groups'=>'post:read']);
    }

    /**
     *@Route("/auth/login", name="login", methods={"POST"})
     */
    public function login(Request $request,UserRepository $userRepository,UserPasswordEncoderInterface $encoder)
    {

        $content = json_decode($request->getContent(),true);
        $user = $userRepository->findOneBy(['email' => $content['email']]);
        if (!$user || !$encoder->isPasswordValid($user, $content['password'])) {
            return $this->json([
                'message' => 'email or password is wrong.',
            ]);
        }
        $payload = [
            "user" => $user->getUsername(),
            "exp"  => (new \DateTime())->modify("+1 day")->getTimestamp(),
        ];
        $jwt =JWT::encode($payload,$this->getParameter('jwt_secret'),'HS256');
        return $this->json([
            'message' => 'success!',
            'token' => sprintf('Bearer %s', $jwt),
        ]);

    }
}
