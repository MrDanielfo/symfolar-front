<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse; 
use AppBundle\Services\Helpers; 
use AppBundle\Services\JwtAuth;
// Librería para validar
use Symfony\Component\Validator\Constraints as Assert; 

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }
    
    
    public function pruebasAction(Request $request) {
        // Los servicios se invocan desde el principio
        $helpers = $this->get(Helpers::class); 
        $jwtAuth = $this->get(JwtAuth::class); 
        
        $token = $request->get("authorization", null);
        
        if($token && $jwtAuth->checkToken($token) == true) {
            
            $em = $this->getDoctrine()->getManager(); 
        
            $userRepo = $em->getRepository('BackendBundle:User');
            $users = $userRepo->findAll(); 
              
            
            return $helpers->json(array(
               'status' => 'success',
                'users' => $users
            ));
            
        } else {
            return $helpers->json(array(
                'status' => 'error',
                'code'   => 400,
                'data'   => "Authorizationnot valid"
            ));
        }
        
         
        
    }
    
    public function loginAction(Request $request) {
        // Se manda a llamar el helper
        $helpers = $this->get(Helpers::class);
        // Se recibe Json por post
        $json = $request->get('json', null);
        // Array a devolver por defecto
        $data = array(
            'status' => 'error',
            'data'   => 'Send json via post'
        );
        
        if($json != null) {
            // Ejecturará login       
            // se convierte json a objeto de php
            $params = json_decode($json);
            
            // primer paso, verificar que contengan algún valor
            $email = (isset($params->email)) ? $params->email : null;
            $password = (isset($params->password)) ? $params->password: null;
            $getHash = (isset($params->getHash)) ? $params->getHash: null;
            
            // Segundo paso         
            $emailConstraint = new Assert\Email();
            $emailConstraint->message = "This email is not valid";
            $validateEmail = $this->get("validator")->validate($email, $emailConstraint);
            
            // Cifrar la contraseña 
            
            $pwd = hash('sha256', $password);
            
            if($email != null && count($validateEmail) == 0 && $password != null) {
                
                $jwtAuth = $this->get(JwtAuth::class); 
                
                if($getHash == null || $getHash == false) {
                    $signup = $jwtAuth->signUp($email, $pwd);
                } else {
                    $signup = $jwtAuth->signUp($email, $pwd, true); 
                }
                           
                return $this->json($signup); 
            } else {
                $data = array(
                    'status' => 'error',
                    'data'   => 'Something is wrong with your email or password'
                );
            }
            
        }       
        return $helpers->json($data); 
    }
    
    
    
}
