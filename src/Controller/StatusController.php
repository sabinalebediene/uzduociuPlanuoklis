<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Status;

class StatusController extends AbstractController
{
    /**
     * @Route("/status", name="status_index", methods={"GET"})
     */
    public function index(Request $r): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); 


        $statuses = $this->getDoctrine()
        ->getRepository(Status::class);

        if ('name_az' == $r->query->get('sort')) {
            $statuses = $statuses->findBy([],['name'=>'asc']);
        }

        elseif ('name_za' == $r->query->get('sort')) {
            $statuses = $statuses->findBy([],['name'=>'desc']);
        }

        else {
            $statuses = $statuses->findAll();
        }
        return $this->render('status/index.html.twig', [
            'statuses' => $statuses,
            'sortBy' => $r->query->get('sort') ?? 'default',
            'success' => $r->getSession()->getFlashBag()->get('success', []),
            'errors' => $r->getSession()->getFlashBag()->get('errors', []),
        ]);
    }

    /**
     * @Route("/status/create", name="status_create", methods={"GET"})
     */
    public function create(Request $r): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); 

        $status_name= $r->getSession()->getFlashBag()->get('status_name', []);

        return $this->render('status/create.html.twig', [
            'errors' => $r->getSession()->getFlashBag()->get('errors', []),
            'status_name' => $status_name[0] ?? '',
        ]);
    }

    /**
     * @Route("/status/store", name="status_store", methods={"POST"})
     */
    public function store(Request $r, ValidatorInterface $validator): Response
    {
        $status = new Status;

        $status->
        setName($r->request->get('status_name'));

        $errors = $validator->validate($status);

        // jei yra error, verciame i string ir ji graziname, parodo error'a
        if (count($errors) > 0) {

            foreach($errors as $error) {
                $r->getSession()->getFlashBag()->add('errors', $error->getMessage());
            }
            // klaidos atveju ivestas vardas ir pavarde lieka
            $r->getSession()->getFlashBag()->add('status_name', $r->request->get('status_name'));
            
            return $this->redirectToRoute('status_create');
            // po rederektinimo pereiname prie create ir ten persiduodam autoriaus name ir surname kintamuosius
        }


        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($status);
        $entityManager->flush();

        // pries sugrizdami i index'a issiunciame zinute

        $r->getSession()->getFlashBag()->add('success', 'Statusas sekmingai pridetas.');

        return $this->redirectToRoute('status_index');
    }

    /**
     * @Route("/status/edit/{id}", name="status_edit", methods={"GET"})
     */
    public function edit(Request $r, int $id): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); 
        
        $status = $this->getDoctrine()
        ->getRepository(Status::class)
        ->find($id); // randame butent ta statusa, kurio id perduodamas

        $status_name= $r->getSession()->getFlashBag()->get('status_name', []);

        // pries sugrizdami i index'a issiunciame acces'o zinute

        $r->getSession()->getFlashBag()->add('success', 'Statusas sekmingai pakeistas.');

        return $this->render('status/edit.html.twig', [
            'status' => $status, // perduodame
            'status_name' => $status_name[0] ?? '',
            'errors' => $r->getSession()->getFlashBag()->get('errors', []),
            'success' => $r->getSession()->getFlashBag()->get('success', []),
        ]);
    }

    /**
     * @Route("/status/update/{id}", name="status_update", methods={"POST"})
     */
    public function update(Request $r, ValidatorInterface $validator, $id): Response
    {

        $status = $this->getDoctrine()
        ->getRepository(Status::class)
        ->find($id);

        $status->
        setName($r->request->get('status_name'));

        $errors = $validator->validate($status);

        // jei yra error, verciame i string ir ji graziname, parodo error'a
        if (count($errors) > 0) {

            foreach($errors as $error) {
                $r->getSession()->getFlashBag()->add('errors', $error->getMessage());
            }
            // klaidos atveju ivestas vardas ir pavarde lieka
            $r->getSession()->getFlashBag()->add('status_name', $r->request->get('status_name'));
            
            return $this->redirectToRoute('status_create');
        }


        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($status);
        $entityManager->flush();

        return $this->redirectToRoute('status_index');
    }

    /**
     * @Route("/status/delete/{id}", name="status_delete", methods={"POST"})
     */
    public function delete(Request $r, $id, ValidatorInterface $validator): Response
    {

        $status = $this->getDoctrine()
            ->getRepository(Status::class)
            ->find($id);
      
        if ($status->getTasks()->count() > 0){
            return new Response('Trinti negalima, nes turi task');
        };

        // remove metodu paduodame ta autoriu ir vykdome
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($status);
        $entityManager->flush();

        $r->getSession()->getFlashBag()->add('success', 'Statusas sekmingai istrintas.');

        return $this->redirectToRoute('status_index');
    }
}

