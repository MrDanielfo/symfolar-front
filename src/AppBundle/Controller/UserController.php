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
use BackendBundle\Entity\User;

class UserController extends Controller {

    public function newAction(Request $request) {

        $helpers = $this->get(Helpers::class);

        $json = $request->get('json', null);
        $params = json_decode($json);

        $data = array(
            'status' => 'error',
            'code' => 400,
            'msg' => 'User not created!!'
        );

        if ($json != null) {

            $createdAt = new \DateTime("now");
            $role = 'user';

            $email = (isset($params->email)) ? $params->email : null;
            $name = (isset($params->name)) ? $params->name : null;
            $surname = (isset($params->surname)) ? $params->surname : null;
            $password = (isset($params->password)) ? $params->password : null;

            $emailConstraint = new Assert\Email();
            $emailConstraint->message = "This email is not valid";
            $validateEmail = $this->get("validator")->validate($email, $emailConstraint);

            if ($email != null && count($validateEmail) == 0 && $password != null && $name != null && $surname != null) {

                $user = new User();
                $user->setCreatedAt($createdAt);
                $user->setRole($role);
                $user->setEmail($email);
                $user->setName($name);
                $user->setSurname($surname);
                
                // Encriptar Password 
                
                $pwd = hash('sha256', $password);
                
                $user->setPassword($pwd);
                
                // Fin de encriptar Password

                $em = $this->getDoctrine()->getManager();

                $isset_user = $em->getRepository('BackendBundle:User')->findBy(array(
                    "email" => $email
                ));

                if (count($isset_user) == 0) {
                    $em->persist($user);
                    $em->flush();

                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'msg' => 'Congratulations: User has been created',
                        'user' => $user
                    );
                } else {
                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'msg' => 'Email already exists!!'
                    );
                }
            } else {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'msg' => 'User not created!!'
                );
            }
        }

        return $helpers->json($data);
    }

    public function editAction(Request $request) {

        $helpers = $this->get(Helpers::class);
        $jwtAuth = $this->get(JwtAuth::class);
        

        $token = $request->get("authorization", null);
        $checkToken = $jwtAuth->checkToken($token);

        if ($checkToken) {
            $json = $request->get('json', null);
            $params = json_decode($json);
            // Llamado al entity Manager
            $em = $this->getDoctrine()->getManager();
            
            // Se establece una variable que contiene los datos decodificados
            
            $identity = $jwtAuth->checkToken($token, true);
            
            // Método para conseguir objeto a actualizar
            
            $user = $em->getRepository('BackendBundle:User')->findOneBy(array(
                'id'    => $identity->sub
            )); 

            $data = array(
                'status' => 'error',
                'code' => 400,
                'msg' => 'User not created!!'
            );

            if ($json != null) {

                // $createdAt = new \DateTime("now");
                $role = 'user';

                $email = (isset($params->email)) ? $params->email : null;
                $name = (isset($params->name)) ? $params->name : null;
                $surname = (isset($params->surname)) ? $params->surname : null;
                $password = (isset($params->password)) ? $params->password : null;

                $emailConstraint = new Assert\Email();
                $emailConstraint->message = "This email is not valid";
                $validateEmail = $this->get("validator")->validate($email, $emailConstraint);

                if ($email != null && count($validateEmail) == 0 && $name != null && $surname != null) {
                    
                    // $user->setCreatedAt($createdAt);
                    $user->setRole($role);
                    $user->setEmail($email);
                    $user->setName($name);
                    $user->setSurname($surname);

                    // Actualizar Password
                    
                    if($password != null) {
                        
                        $pwd = hash('sha256', $password);

                        $user->setPassword($pwd);
                    }
                
                    // Fin de actualizar password

                    $isset_user = $em->getRepository('BackendBundle:User')->findBy(array(
                        "email" => $email
                    ));

                    if (count($isset_user) == 0 || $identity->email == $email) {
                        $em->persist($user);
                        $em->flush();

                        $data = array(
                            'status' => 'success',
                            'code' => 200,
                            'msg' => 'Congratulations: User has been updated',
                            'user' => $user
                        );
                    } else {
                        $data = array(
                            'status' => 'error',
                            'code' => 400,
                            'msg' => 'Email already exists!!'
                        );
                    }
                } else {
                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'msg' => 'User not uptated!!'
                    );
                }
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'msg' => 'Error!!'
            );
        }


        return $helpers->json($data);
    }

}
