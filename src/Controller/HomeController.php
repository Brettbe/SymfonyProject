<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Offer;
use App\Form\OfferType;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        $offers = [
            [
                'title' => 'Offer 1',
                'description' => 'Description of offer 1',
            ],
            [
                'title' => 'Offer 2',
                'description' => 'Description of offer 2',
            ],
            [
                'title' => 'Offer 3',
                'description' => 'Description of offer 3',
            ],
        ];

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'offers' => $offers,
        ]);
    }

    #[Route('/add-offer', name: 'app_add_offer')]
    public function addOffer(EntityManagerInterface $entityManager, Request $request): Response
    {
        $offer = new Offer();
        $form = $this->createForm(OfferType::class, $offer);
        $form->handleRequest($request);

        // dd('test');
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('cv')->getData();


            if(!$file) {
                return new JsonResponse(['error' => 'File not found'], Response::HTTP_BAD_REQUEST);
            }

            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/cv';
            $newFilename = uniqid() . '.' . $file->guessExtension();

            try {
                $file->move($uploadDir, $newFilename);
            } catch (FileException $e) {
                // return new JsonResponse(['error' => 'File upload failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
                return $this->redirectToRoute('app_home');
            }

            // $offer->setDate(new \DateTime());
            // $offer->setRecruiter($this->getUser());
            $offer->setName($form->get('name')->getData());
            $offer->setContent($form->get('content')->getData());
            $offer->setFilename($newFilename);
            $entityManager->persist($offer);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        } else {
            // dd($form->getErrors(true));
        }

        return $this->render('home/addOffer.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // #[Route('/list-offer', name: 'app_list_offer')]
    // public function listOffer(EntityManagerInterface $entityManager, Request $request): Response
    // {
    //     $offers = $entityManager->getRepository(Offer::class)->findAll();

    //     return $this->render('home/listOffers.html.twig', [
    //         'offers' => $offers,
    //     ]);
    // }

    #[Route('list-offer/{tag}', name: 'app_list_offer_by_tag')]
    public function listOfferByTag(EntityManagerInterface $entityManager, string $tag = ''): Response
    {
        if($tag !== "") {
            $offers = $entityManager->getRepository(Offer::class)->findByTag($tag);
        } else {
            $offers = $entityManager->getRepository(Offer::class)->findAll();
        }

        return $this->render('home/listOffers.html.twig', [
            'offers' => $offers,
        ]);
    }

    #[Route('/edit-offer/{id}', name: 'app_edit_offer')]
    public function editOffer(EntityManagerInterface $entityManager, Request $request, string $id): Response
    {
        $offer = $entityManager->getRepository(Offer::class)->find($id);

        $form = $this->createForm(OfferType::class, $offer);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $offer->setRecruiter($this->getUser());
            $entityManager->persist($offer);
            $entityManager->flush();

            return $this->redirectToRoute('app_list_offer_by_tag');
        }

        return $this->render('home/editOffer.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete-offer/{id}', name: 'app_delete_offer')]
    public function deleteOffer(EntityManagerInterface $entityManager, Request $request, string $id): Response
    {
        $offer = $entityManager->getRepository(Offer::class)->find($id);
        $entityManager->remove($offer);
        $entityManager->flush();

        return $this->redirectToRoute('app_list_offer_by_tag');
    }
}
