<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Entity\Cv;


class DefaultController extends Controller
{
    public function homepage()
    {
        return $this->render('Default/homepage.html.twig');
    }



    public function login(AuthenticationUtils $authUtils)
    {
        // get the login error if there is one
        $error = $authUtils->getLastAuthenticationError();
        
        // last username entered by the user
        $lastUsername = $authUtils->getLastUsername();

        return $this->render(
            'Default/login.html.twig',
            array(
                'last_username' => $lastUsername,
                'error' => $error,
            )
        );
    }
  
  
    public function loadCvs()
    {
        $manager = $this->getDoctrine()->getManager();
        $cvs = $manager->getRepository(Cv::class)->findBy(['status' => 1]);
        
        return $this->render(
            'Default/cvtheque.html.twig',
            array(
                'cvs' => $cvs
            )
        );
    }
   
}