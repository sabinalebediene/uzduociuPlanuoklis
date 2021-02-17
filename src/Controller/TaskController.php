<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Task;
use App\Entity\Status;

class TaskController extends AbstractController
{
    /**
     * @Route("/task", name="task_index")
     */
    public function index(Request $r): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); 

        $statuses = $this->getDoctrine()
        ->getRepository(Status::class)
        ->findAll();
        
        // su filtracija
        $tasks = $this->getDoctrine()
        ->getRepository(Task::class);

        if (null !== $r->query->get('status_id')) {
            $tasks = $tasks->findBy(['status_id' => $r->query->get('status_id')]);
        }

        elseif (null !== $r->query->get('task_task_name')) {
            $tasks = $tasks->findBy(['task_task_name' => $r->query->get('task_task_name')]);
        }

        elseif (null !== $r->query->get('task_task_description')) {
            $tasks = $tasks->findBy(['task_task_description' => $r->query->get('task_task_description')]);
        }

        elseif (null !== $r->query->get('task_add_date')) {
            $tasks = $tasks->findBy(['task_add_date' => $r->query->get('task_add_date')]);
        }

        elseif (null !== $r->query->get('task_completed_date')) {
            $tasks = $tasks->findBy(['completed_date' => $r->query->get('task_completed_date')]);
        }
        
        else {
            $tasks = $tasks->findAll();
        }

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
            'statuses' => $statuses,
            'statusId' => $r->query->get('status_id') ?? 0,
            'taskName' => $r->request->get('task_task_name'),
            'taskDescription' => $r->request->get('task_task_description'),
            'addDate' => $r->request->get('task_add_date'),
            'completedDate' => $r->request->get('task_completed_date'),
            'success' => $r->getSession()->getFlashBag()->get('success', [])
        ]);
    }

    /**
     * @Route("/task/create", name="task_create", methods={"GET"})
     */
    public function create(Request $r): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); 

        $statuses = $this->getDoctrine()
        ->getRepository(Status::class)
        ->findAll();


        return $this->render('task/create.html.twig', [
            'errors' => $r->getSession()->getFlashBag()->get('errors', []),
            'statuses' => $statuses,
            'task_status_id' => $task_status_id[0] ?? '',
        ]);
    }

    /**
     * @Route("/task/store", name="task_store", methods={"POST"})
     */
    public function store(Request $r, ValidatorInterface $validator): Response
    {

        $status = $this->getDoctrine()
        ->getRepository(Status::class)
        ->find($r->request->get('tasks_status'));

         // status validacija, jei jis nepaselectintas
         if(null === $status) {
            $r->getSession()->getFlashBag()->add('errors', 'Pasirink statusa');
        }

        $task = new Task;
        $task
        ->setTaskName($r->request->get('task_task_name'))
        ->setTaskDescription($r->request->get('task_task_description'))
        ->setAddDate($r->request->get('task_add_date'))
        ->setCompletedDate($r->request->get('task_completed_date'))
        ->setStatus($status);


        // validacija
        $errors = $validator->validate($task);

        // jei yra error, verciame i string ir ji graziname, parodo error'a
        if (count($errors) > 0 || null === $status) {

            foreach($errors as $error) {
                $r->getSession()->getFlashBag()->add('errors', $error->getMessage());
            }
            $r->getSession()->getFlashBag()->add('task_status_id', $r->request->get('task_status_id'));
            return $this->redirectToRoute('task_create');
            
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($task);
        $entityManager->flush();

        $r->getSession()->getFlashBag()->add('success', 'Užduotis '.$task->getTaskName().' sėkmingai sukurta.');

        return $this->redirectToRoute('task_index');
    }

    /**
     * @Route("/task/edit/{id}", name="task_edit", methods={"GET"})
     */
    public function edit(Request $r, int $id): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); 
        
        $task = $this->getDoctrine()
        ->getRepository(Task::class)
        ->find($id); // randame butent ta autoriu, kurio id perduodamas

        $statuses = $this->getDoctrine()
        ->getRepository(Status::class)
        ->findBy([],['name'=>'asc']);

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'statuses' => $statuses,
            'errors' => $r->getSession()->getFlashBag()->get('errors', []),
            'success' => $r->getSession()->getFlashBag()->get('success', [])
        ]);
    }

    /**
     * @Route("/task/update/{id}", name="task_update", methods={"POST"})
     */
    public function update(Request $r, $id, ValidatorInterface $validator): Response
    {

        $status = $this->getDoctrine()
        ->getRepository(Status::class)
        ->find($r->request->get('tasks_status'));

        $task = $this->getDoctrine()
        ->getRepository(Task::class)
        ->find($id);
    
        $task
        ->setTaskName($r->request->get('task_task_name'))
        ->setAddDate($r->request->get('task_add_date'))
        ->setCompletedDate($r->request->get('task_completed_date'))
        ->setStatus($status);

        $errors = $validator->validate($task);

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $r->getSession()->getFlashBag()->add('errors', $error->getMessage());
            }
            return $this->redirectToRoute('task_edit', ['id'=>$task->getId()]);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($task);
        $entityManager->flush();

        $r->getSession()->getFlashBag()->add('success', 'Task '.$task->getTaskName().' was updated.');

        return $this->redirectToRoute('task_index');
    }

    /**
     * @Route("/task/delete/{id}", name="task_delete", methods={"POST"})
     */
    public function delete(Request $r, $id): Response
    {

        $task = $this->getDoctrine()
        ->getRepository(Task::class)
        ->find($id); // randame butent ta autoriu, kurio id perduodamas

        // remove metodu padauodame ta autoriu ir vykdome
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($task);
        $entityManager->flush();

        $r->getSession()->getFlashBag()->add('success', 'Užduotis '.$task->getTaskName().' sėkmingai ištrinta.');

        return $this->redirectToRoute('task_index');
    }
}
