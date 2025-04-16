<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Offer;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Workflow\Registry;

#[Route('api')]
final class ApiController extends AbstractController
{
    #[Route('/offers', name: 'list', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'List of offers',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Offer::class, groups: ['full'])),
        ),
        )]
    #[OA\Tag(name: 'Offers')]
    #[Security(name: 'Bearer')]
    public function list(Request $request, EntityManagerInterface $em, SerializerInterface $serializer, PaginatorInterface $paginator): JsonResponse
    {
        $query = $em -> getRepository(Offer::class)->createQueryBuilder('c')->getQuery();

        $page = $request->query->getInt('page', 2);

        $pagination = $paginator->paginate(
            $query,
            $page,
            1
        );

        return new JsonResponse([
            'totalItems' => $pagination->getTotalItemCount(),
            'currentPage' => $page,
            'totalPage' => ceil($pagination->getTotalItemCount() / 1),
            'items' => json_decode($serializer->serialize($pagination->getItems(), 'json', ['groups' => 'offer:read']))
        ], 200);
    }

    #[Route('/offers/{id}', name: 'show', methods: ['GET'])]
    #[OA\Tag(name: 'Offers')]
    #[Security(name: 'Bearer')]
    public function show(Offer $offer, SerializerInterface $serializer): JsonResponse
    {
        $data = $serializer->serialize($offer, 'json', ['groups' => 'offer:read']);

        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/offers', name: 'create', methods: ['POST'])]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'date', type: 'string', format: 'date-time'),
                new OA\Property(property: 'content', type: 'string'),
                new OA\Property(property: 'file', type: 'file'),
            ]
        ),
    )]
    #[OA\Tag(name: 'Offers')]
    #[Security(name: 'Bearer')]
    public function create(Request $request, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        $offer = new Offer();
        // $offer = $serializer->deserialize($request->getContent(), Offer::class, 'json');
        $offer->setName($request->request->get('name'));
        // $offer->setDate(new \DateTime($request->request->get('date')));
        $offer->setContent($request->request->get('content'));
        $user = $this->getUser();
        $offer->setRecruiter($user);

        $file = $request->files->get('file');

        if(!$file) {
            return new JsonResponse(['error' => 'File not found'], Response::HTTP_BAD_REQUEST);
        }

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/cv';

        $newFilename = uniqid() . '.' . $file->guessExtension();

        try {
            $file->move($uploadDir, $newFilename);
        } catch (FileException $e) {
            return new JsonResponse(['error' => 'File upload failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $offer->setFilename($newFilename);
        $em->persist($offer);
        $em->flush();

        return new JsonResponse(null, 201);
    }

    #[Route('/offers/{id}', name: 'update', methods: ['PUT'])]
    #[OA\Tag(name: 'Offers')]
    #[Security(name: 'Bearer')]
    public function update(Offer $offer, Request $request, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        $serializer->deserialize($request->getContent(), Offer::class, 'json', ['object_to_populate' => $offer]);
        $em->flush();

        return new JsonResponse(null, 200);
    }

    #[Route('/offers/{id}', name: 'delete', methods: ['PATCH'])]
    #[OA\Tag(name: 'Offers')]
    #[Security(name: 'Bearer')]
    public function patch(Offer $offer, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        $serializer->deserialize($request->getContent(), Offer::class, 'json', ['object_to_populate' => $offer]);
        $em->flush();

        return new JsonResponse(null, 200);
    }

    #[Route('/offers/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Tag(name: 'Offers')]
    #[Security(name: 'Bearer')]
    public function delete(Offer $offer, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($offer);
        $em->flush();

        return new JsonResponse(null, 204);
    }

    #[Route('/offers/transition/{id}', methods: ['POST'])]
    public function transition(Offer $offer, Request $request, Registry $workflowRegistry, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('CAN_TRANSITION', $offer);
        $data = json_decode($request->getContent(), true);
        $transition = $data['transition'] ?? null;

        $workflow = $workflowRegistry->get($offer, 'offer_status');

        if (!$transition || !$workflow->can($offer, $transition)) {
            return new JsonResponse(['error' => 'Transition not allowed'], 400);
        }

        $workflow->apply($offer, $transition);
        $em->flush();

        return new JsonResponse(['status' => $offer->getStatus()]);
    }

    #[Route('/users', name: 'usersList', methods: ['GET'])]
    public function usersList(Request $request, EntityManagerInterface $em, SerializerInterface $serializer, PaginatorInterface $paginator): JsonResponse
    {
        $query = $em->getRepository(User::class)->createQueryBuilder('c')->getQuery();

        $page = $request->query->getInt('page', 1);

        $pagination = $paginator->paginate(
            $query,
            $page,
            10
        );

        return new JsonResponse([
            'totalItems' => $pagination->getTotalItemCount(),
            'currentPage' => $page,
            'totalPage' => ceil($pagination->getTotalItemCount() / 10),
            'items' => json_decode($serializer->serialize($pagination->getItems(), 'json', ['groups' => 'user:read']))
        ], 200);
    }
}
