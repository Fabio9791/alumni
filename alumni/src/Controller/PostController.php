<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Post;
use App\Form\PostFormType;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use App\DTO\PostSearch;
use App\Service\MessageSender;


class PostController extends Controller
{
    public function createPost(Request $request,MessageSender $messageSender){
        $post = new Post();
        $user = $this->getUser();
        $post->setAuthor($user);
        $form = $this->createForm(
            PostFormType::class,
            $post,
            [
                'standalone' => true,
                'user' => $user
            ]
        );
        
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            // insert data in database
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($post);
            $manager->flush();
            // redirect to user list GET
            return $this->redirectToRoute('post');
        }
        return $this->render(
            'Default\post.html.twig',
            [
                'post_form' => $form->createView(), 
                'user' => $user      
            ]
        );
    }
  
    public function getPosts(\DateTime $creationDate)
    {
        // $time = new \DateTime();
        // $time->setTimestamp($creationDate);
        $posts = $this->getDoctrine()
            ->getManager()
            ->getRepository(Post::class);
        
        $postSearch = new PostSearch();

        $postSearch->creationDate=$creationDate;
        $postSearch->user=null;
        $postSearch->groups=$this->getUser()->getVisibilityGroups();
        
        $postList = $posts->findByDate($postSearch);
        $serializer = $this->getSerializer();
        $data = $serializer->serialize(
            $postList,
            'json', 
            array(
                'groups' => array('posts')
            )
        );

        return new JsonResponse(
            $data,
            200,
            [],
            true
        );
    }


    public function listPosts(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $posts = $manager->getRepository(Post::class)->findAll();
    
        return $this->render(
            'Postlist/test.html.twig',
            [
                'posts' =>  $posts,
            ]
        );
    }

    public function flagPost(Request $request, Post $post)
    {
        $manager = $this->getDoctrine()->getManager();
        $post->setFlag(1);
        
        $manager->persist($post);
        $manager->flush();

        $serializer = $this->getSerializer();

        return new JsonResponse(
            $serializer->serialize("The post was successfully flagged", 'json'),
            200,
            [],
            true
        );  
    }
    public function getSerializer() : SerializerInterface
    {
        return $this->get('serializer');
    }
}