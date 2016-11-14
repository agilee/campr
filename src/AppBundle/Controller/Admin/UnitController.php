<?php

namespace AppBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Unit;
use AppBundle\Form\Unit\CreateType;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin/unit")
 */
class UnitController extends Controller
{
    /**
     * @Route("/list", name="app_admin_unit_list")
     * @Method({"GET"})
     *
     * @return Response
     */
    public function listAction()
    {
        $units = $this
            ->getDoctrine()
            ->getRepository(Unit::class)
            ->findAll()
        ;

        return $this->render(
            'AppBundle:Admin/Unit:list.html.twig',
            [
                'units' => $units,
            ]
        );
    }

    /**
     * @Route("/list/filtered", name="app_admin_unit_list_filtered", options={"expose"=true})
     * @Method("POST")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listByPageAction(Request $request)
    {
        $requestParams = $request->request->all();
        $dataTableService = $this->get('app.service.data_table');
        $response = $dataTableService->paginateByColumn(Unit::class, 'name', $requestParams);

        return new JsonResponse($response);
    }

    /**
     * Displays Unit entity.
     *
     * @Route("/{id}/show", name="app_admin_unit_show", options={"expose"=true})
     * @Method({"GET"})
     *
     * @param Unit $unit
     *
     * @return Response
     */
    public function showAction(Unit $unit)
    {
        return $this->render(
            'AppBundle:Admin/Unit:show.html.twig',
            [
                'unit' => $unit,
            ]
        );
    }

    /**
     * @Route("/create", name="app_admin_unit_create")
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     *
     * @return Response|RedirectResponse
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(CreateType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($form->getData());
            $em->flush();

            $this
                ->get('session')
                ->getFlashBag()
                ->set(
                    'success',
                    $this
                        ->get('translator')
                        ->trans('admin.unit.create.success', [], 'admin')
                )
            ;

            return $this->redirectToRoute('app_admin_unit_list');
        }

        return $this->render(
            'AppBundle:Admin/Unit:create.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}/edit", name="app_admin_unit_edit", options={"expose"=true})
     * @Method({"GET", "POST"})
     *
     * @param Unit    $unit
     * @param Request $request
     *
     * @return Response|RedirectResponse
     */
    public function editAction(Unit $unit, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(CreateType::class, $unit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $unit->setUpdatedAt(new \DateTime());
            $em->persist($unit);
            $em->flush();

            $this
                ->get('session')
                ->getFlashBag()
                ->set(
                    'success',
                    $this
                        ->get('translator')
                        ->trans('admin.unit.edit.success', [], 'admin')
                )
            ;

            return $this->redirectToRoute('app_admin_unit_list');
        }

        return $this->render(
            'AppBundle:Admin/Unit:edit.html.twig',
            [
                'id' => $unit->getId(),
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}/delete", name="app_admin_unit_delete", options={"expose"=true})
     * @Method({"GET"})
     *
     * @param Unit $unit
     *
     * @return RedirectResponse
     */
    public function deleteAction(Unit $unit)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($unit);
        $em->flush();

        $this
            ->get('session')
            ->getFlashBag()
            ->set(
                'success',
                $this
                    ->get('translator')
                    ->trans('admin.unit.delete.success.general', [], 'admin')
            )
        ;

        return $this->redirectToRoute('app_admin_unit_list');
    }
}
