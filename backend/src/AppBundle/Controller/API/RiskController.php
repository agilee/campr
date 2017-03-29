<?php

namespace AppBundle\Controller\API;

use AppBundle\Entity\Risk;
use AppBundle\Form\Risk\CreateType;
use MainBundle\Controller\API\ApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/api/risks")
 */
class RiskController extends ApiController
{
    /**
     * Get all risks.
     *
     * @Route(name="app_api_risks_list")
     * @Method({"GET"})
     *
     * @return JsonResponse
     */
    public function listAction()
    {
        $risks = $this
            ->getDoctrine()
            ->getRepository(Risk::class)
            ->findAll()
        ;

        return $this->createApiResponse($risks);
    }

    /**
     * Create a new Risk.
     *
     * @Route(name="app_api_risks_create")
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
     * Get Risk by id.
     *
     * @Route("/{id}", name="app_api_risks_get")
     * @Method({"GET"})
     *
     * @param Risk $risk
     *
     * @return JsonResponse
     */
    public function getAction(Risk $risk)
    {
        return $this->createApiResponse($risk);
    }

    /**
     * Edit a specific Risk.
     *
     * @Route("/{id}", name="app_api_risks_edit")
     * @Method({"PUT", "PATCH"})
     *
     * @param Request $request
     * @param Risk    $risk
     *
     * @return JsonResponse
     */
    public function editAction(Request $request, Risk $risk)
    {
        $form = $this->createForm(CreateType::class, $risk, ['csrf_protection' => false]);
        $this->processForm($request, $form, $request->isMethod(Request::METHOD_PUT));

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($risk);
            $em->flush();

            return $this->createApiResponse($risk, Response::HTTP_ACCEPTED);
        }

        $errors = $this->getFormErrors($form);
        $errors = [
            'messages' => $errors,
        ];

        return $this->createApiResponse($errors, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Delete a specific Risk.
     *
     * @Route("/{id}", name="app_api_risks_delete")
     * @Method({"DELETE"})
     *
     * @param Risk $risk
     *
     * @return JsonResponse
     */
    public function deleteAction(Risk $risk)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($risk);
        $em->flush();

        return $this->createApiResponse(null, Response::HTTP_NO_CONTENT);
    }
}