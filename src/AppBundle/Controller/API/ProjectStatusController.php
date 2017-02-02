<?php

namespace AppBundle\Controller\API;

use AppBundle\Entity\ProjectStatus;
use AppBundle\Form\ProjectStatus\CreateType;
use AppBundle\Security\ProjectVoter;
use MainBundle\Controller\API\ApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/api/project-status")
 */
class ProjectStatusController extends ApiController
{
    /**
     * Get all project status.
     *
     * @Route("/list", name="app_api_project_status_list")
     * @Method({"GET"})
     *
     * @return JsonResponse
     */
    public function listAction()
    {
        $projectStatus = $this
            ->getDoctrine()
            ->getRepository(ProjectStatus::class)
            ->findAll()
        ;

        return $this->createApiResponse($projectStatus);
    }

    /**
     * Create a new Project Status.
     *
     * @Route("/create", name="app_api_project_status_create")
     * @Method({"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createAction(Request $request)
    {
        $form = $this->createForm(CreateType::class, null, ['csrf_protection' => false]);
        $this->processForm($request, $form);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();

            return $this->createApiResponse($form->getData(), Response::HTTP_CREATED);
        }

        $errors = $this->getFormErrors($form);
        $errors = [
            'messages' => $errors,
        ];

        return $this->createApiResponse($errors, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Get Project Status by id.
     *
     * @Route("/{id}", name="app_api_project_status_get")
     * @Method({"GET"})
     *
     * @param ProjectStatus $projectStatus
     *
     * @return JsonResponse
     */
    public function getAction(ProjectStatus $projectStatus)
    {
        $this->denyAccessUnlessGranted(ProjectVoter::VIEW, $projectStatus->getProject());

        return $this->createApiResponse($projectStatus);
    }

    /**
     * Edit a specific Project Status.
     *
     * @Route("/{id}/edit", name="app_api_project_status_edit")
     * @Method({"PATCH"})
     *
     * @param Request       $request
     * @param ProjectStatus $projectStatus
     *
     * @return JsonResponse
     */
    public function editAction(Request $request, ProjectStatus $projectStatus)
    {
        $this->denyAccessUnlessGranted(ProjectVoter::EDIT, $projectStatus->getProject());

        $form = $this->createForm(CreateType::class, $projectStatus, ['csrf_protection' => false]);
        $this->processForm($request, $form, false);

        if ($form->isValid()) {
            $projectStatus->setUpdatedAt(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($projectStatus);
            $em->flush();

            return $this->createApiResponse($projectStatus, Response::HTTP_ACCEPTED);
        }

        $errors = $this->getFormErrors($form);
        $errors = [
            'messages' => $errors,
        ];

        return $this->createApiResponse($errors, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Delete a specific Project Status.
     *
     * @Route("/{id}/delete", name="app_api_project_status_delete")
     * @Method({"DELETE"})
     *
     * @param ProjectStatus $projectStatus
     *
     * @return JsonResponse
     */
    public function deleteAction(ProjectStatus $projectStatus)
    {
        $this->denyAccessUnlessGranted(ProjectVoter::DELETE, $projectStatus->getProject());

        $em = $this->getDoctrine()->getManager();
        $em->remove($projectStatus);
        $em->flush();

        return $this->createApiResponse(null, Response::HTTP_NO_CONTENT);
    }
}
