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
use BackendBundle\Entity\Task;

class TaskController extends Controller {

    public function newAction(Request $request, $id = null) {
        $helpers = $this->get(Helpers::class); 
        $jwtAuth = $this->get(JwtAuth::class); 
        
        $token = $request->get("authorization", null);
        $checkToken = $jwtAuth->checkToken($token);
        
        if($checkToken) {
            $identity = $jwtAuth->checkToken($token, true);
            $json = $request->get('json', null); 
            
            if($json != null) {
                // Crea tarea
                
                $params = json_decode($json);
                
                $createdAt = new \Datetime('now');
                $updatedAt = new \Datetime('now');
                
                $user_id = ($identity->sub != null) ? $identity->sub : null;
                $title = (isset($params->title)) ? $params->title : null;
                $description = (isset($params->description)) ? $params->description : null;
                $status = (isset($params->status)) ? $params->status : null;
                
                if($user_id != null && $title != null) {
                    
                    $em = $this->getDoctrine()->getManager();
                    
                    $user = $em->getRepository('BackendBundle:User')->findOneBy(array(
                        'id' => $user_id
                    )); 
                    
                    if($id == null) {
                        $task = new Task(); 
                        $task->setUser($user);
                        $task->setTitle($title);
                        $task->setDescription($description);
                        $task->setStatus($status);
                        $task->setCreatedAt($createdAt);
                        $task->setUpdatedAt($updatedAt); 

                        $em->persist($task);
                        $em->flush();
                    
                        $data = array(
                            'status'    => 'success',
                            'code'      => 200,
                            'message'   => 'Task created successfully',
                            'data'      => $task
                        );
                    } else {
                        
                        $task = $em->getRepository('BackendBundle:Task')->findOneBy(array(
                           'id' => $id 
                        ));
                        
                        if(isset($identity->sub) && $identity->sub == $task->getUser()->getId()) {
                        
                        $task->setTitle($title);
                        $task->setDescription($description);
                        $task->setStatus($status);
                        $task->setUpdatedAt($updatedAt); 

                        $em->persist($task);
                        $em->flush();
                    
                        $data = array(
                            'status'    => 'success',
                            'code'      => 200,
                            'message'   => 'Task updated successfully',
                            'data'      => $task
                        );
                        
                        
                    } else {
                            $data = array(
                                'status'    => 'error',
                                'code'      => 400,
                                'message'   => 'you are not the owner of the task'
                            );
                        }   
                                
                    } 
                    
                } else {
                    $data = array(
                        'status'    => 'error',
                        'code'      => 400,
                        'message'   => 'Problems with the id or title'
                    );
                }
                
                        
            } else {
                $data = array(
                    'status'    => 'error',
                    'code'      => 400,
                    'message'   => 'Task not created'
                );
            }
            
            
        } else {
            $data = array(
                'status'    => 'error',
                'code'      => 400,
                'message'   => 'Authorization not valid'
            );
        }
        
        return $helpers->json($data); 
        
    }
    
    public function taskListAction(Request $request) {
        
        $helpers = $this->get(Helpers::class); 
        $jwtAuth = $this->get(JwtAuth::class); 
        
        $token = $request->get("authorization", null);
        $checkToken = $jwtAuth->checkToken($token);
        
        if($checkToken) {
            $identity = $jwtAuth->checkToken($token, true);
            $em = $this->getDoctrine()->getManager(); 
            
            $dql = "SELECT t FROM BackendBundle:Task t WHERE t.user = {$identity->sub} ORDER BY t.id DESC";
            $query = $em->createQuery($dql);
            $page = $request->query->getInt('page', 1);
            $paginator = $this->get('knp_paginator');
            $items_per_page = 10;
            
            $pagination = $paginator->paginate($query, $page, $items_per_page);
            $total_items_count = $pagination->getTotalItemCount();
            
            
                $data = array(
                    'status'            => 'success',
                    'code'              => 200,
                    'message'           => 'The tasks list',
                    'total_items_count' => $total_items_count,
                    'actual_page'       => $page,
                    'items_per_page'    => $items_per_page,
                    'total_pages'       => ceil($total_items_count / $items_per_page),
                    'data'              => $pagination
                );
        
        } else {
            $data = array(
                'status'    => 'error',
                'code'      => 400,
                'message'   => 'Authorization not valid'
            );
        }
        
        
        return $helpers->json($data); 
    
    }
    
    
    public function taskSingleAction(Request $request, $id = null) {
        
        $helpers = $this->get(Helpers::class); 
        $jwtAuth = $this->get(JwtAuth::class); 
        
        $token = $request->get("authorization", null);
        $checkToken = $jwtAuth->checkToken($token);
        
        if($checkToken) {
            $identity = $jwtAuth->checkToken($token, true);
            $em = $this->getDoctrine()->getManager();
            
            $task = $em->getRepository('BackendBundle:Task')->findOneBy(array(
                           'id' => $id 
                    ));
            
            if($task && is_object($task) && $identity->sub == $task->getUser()->getId()) {
                        
                    
                $data = array(
                    'status'    => 'success',
                    'code'      => 200,
                    'message'   => 'Task showed successfully',
                    'data'      => $task
                );


                } else {
                    $data = array(
                        'status'    => 'error',
                        'code'      => 400,
                        'message'   => 'you are not the owner of the task'
                    );
                }
            
              
        } else {
            $data = array(
                'status'    => 'error',
                'code'      => 400,
                'message'   => 'Authorization not valid'
            );
        }
        
        return $helpers->json($data);
        
    }
    
    public function searchAction(Request $request, $search = null) {
        $helpers = $this->get(Helpers::class); 
        $jwtAuth = $this->get(JwtAuth::class); 
        
        $token = $request->get("authorization", null);
        $checkToken = $jwtAuth->checkToken($token);
        
        if($checkToken) {
            
            $identity = $jwtAuth->checkToken($token, true);
            $em = $this->getDoctrine()->getManager();
            
            // Filtro
            $filter = $request->get('filter', null);
            if(empty($filter)){
                $filter = null;
            } elseif($filter == 1) {
                $filter = 'new';
            } elseif($filter == 2) {
                $filter = 'toDo';
            } else {
                $filter = 'finished';
            }
            
            // Orden
            
            $order = $request->get('order', null);
            if(empty($order) || $order == 2) {
                $order = 'DESC';
            } else {
                $order = 'ASC'; 
            }
            
            // Búsqueda
            if($search != null) {
                $dql = "SELECT t FROM BackendBundle:Task t WHERE t.user = $identity->sub AND " . "(t.title LIKE :search OR t.description LIKE :search) ";
                
            } else {
                $dql = "SELECT t FROM BackendBundle:Task t WHERE t.user = $identity->sub";
                
            }
            
            // Set Filter
            
            if($filter != null) {
                    $dql .= " AND t.status = :filter";
                }
                
            // set order
            
            $dql .= " ORDER BY t.id $order"; 
            
            // create Query
            
            $query = $em->createQuery($dql);
            
            // Set Parameter Filter
            
            if($filter != null) {
                $query->setParameter('filter', "$filter");
            }
             
            // Set Parameter Search 
            
            if(!empty($search)) {
                $query->setParameter('search', "%$search%");
            } 
            
            $tasks = $query->getResult(); 
            
            $data = array(
                'status'    => 'success',
                'code'      => 200,
                'tasks'    => $tasks
            );
            
        } else {
            $data = array(
                'status'    => 'error',
                'code'      => 400,
                'message'   => 'Authorization not valid'
            );
        }
            
            
            return $helpers->json($data);  
        
        }
        
    public function taskRemoveAction(Request $request, $id = null) {
        
        $helpers = $this->get(Helpers::class); 
        $jwtAuth = $this->get(JwtAuth::class); 
        
        $token = $request->get("authorization", null);
        $checkToken = $jwtAuth->checkToken($token);
        
        if($checkToken) {
            $identity = $jwtAuth->checkToken($token, true);
            $em = $this->getDoctrine()->getManager();
            
            $task = $em->getRepository('BackendBundle:Task')->findOneBy(array(
                           'id' => $id 
                    ));
            
            if($task && is_object($task) && $identity->sub == $task->getUser()->getId()) {
                
                $data = array(
                    'status'    => 'success',
                    'code'      => 200,
                    'message'   => 'Task deleted successfully',
                    'data'      => $task
                );
                 
                $em->remove($task);
                $em->flush();
                     
                } else {
                    $data = array(
                        'status'    => 'error',
                        'code'      => 400,
                        'message'   => 'you are not the owner of the task'
                    );
                }
            
              
        } else {
            $data = array(
                'status'    => 'error',
                'code'      => 400,
                'message'   => 'Authorization not valid'
            );
        }
        
        return $helpers->json($data);
        
    }
        

}
