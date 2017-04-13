<?php

namespace AppBundle\Controller\API;

use AppBundle\Entity\Calendar;
use AppBundle\Entity\Contract;
use AppBundle\Entity\DistributionList;
use AppBundle\Entity\Label;
use AppBundle\Entity\Meeting;
use AppBundle\Entity\Note;
use AppBundle\Entity\Project;
use AppBundle\Entity\ProjectDeliverable;
use AppBundle\Entity\ProjectLimitation;
use AppBundle\Entity\ProjectObjective;
use AppBundle\Entity\ProjectStatus;
use AppBundle\Entity\ProjectTeam;
use AppBundle\Entity\ProjectUser;
use AppBundle\Entity\Todo;
use AppBundle\Entity\WorkPackage;
use AppBundle\Entity\Unit;
use AppBundle\Entity\WorkPackageProjectWorkCostType;
use AppBundle\Form\Label\BaseLabelType;
use AppBundle\Form\Project\CreateType;
use AppBundle\Form\Calendar\BaseCreateType as CalendarCreateType;
use AppBundle\Form\Contract\BaseCreateType as ContractCreateType;
use AppBundle\Form\DistributionList\BaseCreateType as DistributionCreateType;
use AppBundle\Form\Meeting\BaseCreateType as MeetingCreateType;
use AppBundle\Form\Note\BaseCreateType as NoteCreateType;
use AppBundle\Form\ProjectTeam\BaseCreateType as ProjectTeamCreateType;
use AppBundle\Form\ProjectUser\BaseCreateType as ProjectUserCreateType;
use AppBundle\Form\Todo\BaseCreateType as TodoCreateType;
use AppBundle\Form\ProjectObjective\CreateType as ProjectObjectiveCreateType;
use AppBundle\Form\ProjectDeliverable\CreateType as ProjectDeliverableCreateType;
use AppBundle\Form\ProjectLimitation\CreateType as ProjectLimitationCreateType;
use AppBundle\Form\Unit\CreateType as UnitCreateType;
use AppBundle\Form\WorkPackage\ApiCreateType;
use AppBundle\Security\ProjectVoter;
use MainBundle\Controller\API\ApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/api/projects")
 */
class ProjectController extends ApiController
{
    /**
     * TODO: Add filters.
     *
     * Get all projects.
     *
     * @Route(name="app_api_project_list", options={"expose"=true})
     * @Method({"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $filters = $request->query->all();

        $projects = $em
            ->getRepository(Project::class)
            ->findByUserAndFilters($this->getUser(), $filters)
        ;

        $responseArray = [];
        $responseArray['totalItems'] = count($projects);
        $responseArray['items'] = $projects;

        return $this->createApiResponse($responseArray);
    }

    /**
     * Create a new Project.
     *
     * @Route(name="app_api_project_create", options={"expose"=true})
     * @Method({"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createAction(Request $request)
    {
        $project = new Project();
        $form = $this->createForm(CreateType::class, $project, ['csrf_protection' => false]);
        $this->processForm($request, $form);

        $project->setLogoFile($request->files->get('logoFile'));

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if (!$project->getStatus()) {
                $projectStatus = $em
                    ->getRepository(ProjectStatus::class)
                    ->find(ProjectStatus::STATUS_NOT_STARTED)
                ;

                $project->setStatus($projectStatus);
            }

            $em->persist($project);
            $em->flush();

            return $this->createApiResponse($project, Response::HTTP_CREATED);
        }

        $errors = $this->getFormErrors($form);
        $errors = [
            'messages' => $errors,
        ];

        return $this->createApiResponse($errors, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Get Project by id.
     *
     * @Route("/{id}", name="app_api_project_get", options={"expose"=true})
     * @Method({"GET"})
     *
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function getAction(Project $project)
    {
        $this->denyAccessUnlessGranted(ProjectVoter::VIEW, $project);

        return $this->createApiResponse($project);
    }

    /**
     * Edit a specific Project.
     *
     * @Route("/{id}", name="app_api_project_edit", options={"expose"=true})
     * @Method({"PUT", "PATCH"})
     *
     * @param Request $request
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function editAction(Request $request, Project $project)
    {
        $this->denyAccessUnlessGranted(ProjectVoter::EDIT, $project);

        $form = $this->createForm(CreateType::class, $project, ['csrf_protection' => false]);
        $this->processForm($request, $form, $request->isMethod(Request::METHOD_PUT));

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($project);
            $em->flush();

            return $this->createApiResponse($project, Response::HTTP_ACCEPTED);
        }

        $errors = $this->getFormErrors($form);
        $errors = [
            'messages' => $errors,
        ];

        return $this->createApiResponse($errors, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Delete a specific Project.
     *
     * @Route("/{id}", name="app_api_project_delete")
     * @Method({"DELETE"})
     *
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function deleteAction(Project $project)
    {
        $this->denyAccessUnlessGranted(ProjectVoter::DELETE, $project);

        $em = $this->getDoctrine()->getManager();
        $em->remove($project);
        $em->flush();

        return $this->createApiResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * All Calendars for a specific Project.
     *
     * @Route("/{id}/calendars", name="app_api_project_calendars")
     * @Method({"GET"})
     *
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function calendarsAction(Project $project)
    {
        return $this->createApiResponse($project->getCalendars());
    }

    /**
     * Create a new Calendar.
     *
     * @Route("/{id}/calendars", name="app_api_project_create_calendar")
     * @Method({"POST"})
     *
     * @param Request $request
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function createCalendarAction(Request $request, Project $project)
    {
        $calendar = new Calendar();
        $calendar->setProject($project);

        $form = $this->createForm(CalendarCreateType::class, $calendar, ['csrf_protection' => false]);
        $this->processForm($request, $form);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($calendar);
            $em->flush();

            return $this->createApiResponse($calendar, Response::HTTP_CREATED);
        }

        $errors = $this->getFormErrors($form);
        $errors = [
            'messages' => $errors,
        ];

        return $this->createApiResponse($errors, Response::HTTP_BAD_REQUEST);
    }

    /**
     * All Contracts for a specific Project.
     *
     * @Route("/{id}/contracts", name="app_api_project_contracts", options={"expose"=true})
     * @Method({"GET"})
     *
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function contractsAction(Project $project)
    {
        return $this->createApiResponse($project->getContracts());
    }

    /**
     * Create a new Contract.
     *
     * @Route("/{id}/contracts", name="app_api_project_create_contract", options={"expose"=true})
     * @Method({"POST"})
     *
     * @param Request $request
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function createContractAction(Request $request, Project $project)
    {
        $contract = new Contract();
        $contract->setProject($project);

        $form = $this->createForm(ContractCreateType::class, $contract, ['csrf_protection' => false]);
        $this->processForm($request, $form);

        if ($form->isValid()) {
            $contract->setCreatedBy($this->getUser());

            $em = $this->getDoctrine()->getManager();
            $em->persist($contract);
            $em->flush();

            return $this->createApiResponse($contract, Response::HTTP_CREATED);
        }

        $errors = $this->getFormErrors($form);
        $errors = [
            'messages' => $errors,
        ];

        return $this->createApiResponse($errors, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Get all distribution lists for a specific Project.
     *
     * @Route("/{id}/distribution-lists", name="app_api_project_distribution_lists")
     * @Method({"GET"})
     *
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function distributionListsAction(Project $project)
    {
        return $this->createApiResponse($project->getDistributionLists());
    }

    /**
     * Create a new DistributionList.
     *
     * @Route("/{id}/distribution-lists", name="app_api_project_distribution_list_create")
     * @Method({"POST"})
     *
     * @param Request $request
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function createDistributionListAction(Request $request, Project $project)
    {
        $distributionList = new DistributionList();
        $distributionList->setCreatedBy($this->getUser());
        $distributionList->setProject($project);

        $form = $this->createForm(DistributionCreateType::class, $distributionList, ['csrf_protection' => false]);
        $this->processForm($request, $form);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($distributionList);
            $em->flush();

            return $this->createApiResponse($distributionList, JsonResponse::HTTP_CREATED);
        }

        $errors = $this->getFormErrors($form);
        $errors = [
            'messages' => $errors,
        ];

        return $this->createApiResponse($errors, Response::HTTP_BAD_REQUEST);
    }

    /**
     * All labels for a specific Project.
     *
     * @Route("/{id}/labels", name="app_api_project_labels", options={"expose"=true})
     * @Method({"GET"})
     *
     * @return JsonResponse
     */
    public function labelsAction(Project $project)
    {
        return $this->createApiResponse($project->getLabels());
    }

    /**
     * Create a new Label.
     *
     * @Route("/{id}/labels", name="app_api_project_create_label", options={"expose"=true})
     * @Method({"POST"})
     *
     * @param Request $request
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function createLabelAction(Request $request, Project $project)
    {
        $label = new Label();
        $label->setProject($project);

        $form = $this->createForm(BaseLabelType::class, $label, ['csrf_protection' => false]);
        $this->processForm($request, $form);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($label);
            $em->flush();

            return $this->createApiResponse($label, Response::HTTP_CREATED);
        }

        $errors = $this->getFormErrors($form);
        $errors = [
            'messages' => $errors,
        ];

        return $this->createApiResponse($errors, Response::HTTP_BAD_REQUEST);
    }

    /**
     * All meetings for a specific Project.
     *
     * @Route("/{id}/meetings", name="app_api_project_meetings")
     * @Method({"GET"})
     *
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function meetingsAction(Project $project)
    {
        return $this->createApiResponse($project->getMeetings());
    }

    /**
     * Create a new Meeting.
     *
     * @Route("/{id}/meetings", name="app_api_project_meeting_create")
     * @Method({"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createMeetingAction(Request $request, Project $project)
    {
        $meeting = new Meeting();
        $meeting->setProject($project);

        $form = $this->createForm(MeetingCreateType::class, $meeting, ['csrf_protection' => false]);
        $this->processForm($request, $form);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($meeting);
            $em->flush();

            return $this->createApiResponse($meeting, Response::HTTP_CREATED);
        }

        $errors = $this->getFormErrors($form);
        $errors = [
            'messages' => $errors,
        ];

        return $this->createApiResponse($errors, Response::HTTP_BAD_REQUEST);
    }

    /**
     * All notes for the current project.
     *
     * @Route("/{id}/notes", name="app_api_project_notes")
     * @Method({"GET"})
     *
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function notesAction(Project $project)
    {
        return $this->createApiResponse($project->getNotes());
    }

    /**
     * Create a new Note.
     *
     * @Route("/{id}/notes", name="app_api_project_note_create")
     * @Method({"POST"})
     *
     * @param Request $request
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function createNoteAction(Request $request, Project $project)
    {
        $note = new Note();
        $note->setProject($project);

        $form = $this->createForm(NoteCreateType::class, $note, ['csrf_protection' => false]);
        $this->processForm($request, $form);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($note);
            $em->flush();

            return $this->createApiResponse($note, Response::HTTP_CREATED);
        }

        $errors = $this->getFormErrors($form);
        $errors = [
            'messages' => $errors,
        ];

        return $this->createApiResponse($errors, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Get all project teams.
     *
     * @Route("/{id}/project-teams", name="app_api_project_project_teams")
     * @Method({"GET"})
     *
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function projectTeamsAction(Project $project)
    {
        return $this->createApiResponse($project->getProjectTeams());
    }

    /**
     * Create a new Project Team.
     *
     * @Route("/{id}/project-teams", name="app_api_project_project_team_create")
     * @Method({"POST"})
     *
     * @param Request $request
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function createProjectTeamAction(Request $request, Project $project)
    {
        $projectTeam = new ProjectTeam();
        $projectTeam->setProject($project);

        $form = $this->createForm(ProjectTeamCreateType::class, $projectTeam, ['csrf_protection' => false]);
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
     * Get all project users.
     *
     * @Route("/{id}/project-users", name="app_api_project_project_users", options={"expose"=true})
     * @Method({"GET"})
     *
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function projectUsersAction(Project $project)
    {
        return $this->createApiResponse($project->getProjectUsers());
    }

    /**
     * Create a new Project User.
     *
     * @Route("/{id}/project-users", name="app_api_project_project_user_create")
     * @Method({"POST"})
     *
     * @param Request $request
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function createProjectUserAction(Request $request, Project $project)
    {
        $projectUser = new ProjectUser();
        $projectUser->setProject($project);

        $form = $this->createForm(ProjectUserCreateType::class, $projectUser, ['csrf_protection' => false]);
        $this->processForm($request, $form);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($projectUser);
            $em->flush();

            return $this->createApiResponse($projectUser, JsonResponse::HTTP_CREATED);
        }

        $errors = $this->getFormErrors($form);
        $errors = [
            'messages' => $errors,
        ];

        return $this->createApiResponse($errors, Response::HTTP_BAD_REQUEST);
    }

    /**
     * All project Todos.
     *
     * @Route("/{id}/todos", name="app_api_projects_todos")
     * @Method({"GET"})
     *
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function todosAction(Project $project)
    {
        return $this->createApiResponse($project->getTodos());
    }

    /**
     * Create a new Todo.
     *
     * @Route("/{id}/todos", name="app_api_projects_todo_create")
     * @Method({"POST"})
     *
     * @param Request $request
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function createTodoAction(Request $request, Project $project)
    {
        $todo = new Todo();
        $todo->setProject($project);

        $form = $this->createForm(TodoCreateType::class, $todo, ['csrf_protection' => false]);
        $this->processForm($request, $form);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($todo);
            $em->flush();

            return $this->createApiResponse($todo, Response::HTTP_CREATED);
        }

        $errors = $this->getFormErrors($form);
        $errors = [
            'messages' => $errors,
        ];

        return $this->createApiResponse($errors, Response::HTTP_BAD_REQUEST);
    }

    /**
     * All wppwct for a specific Project.
     *
     * @Route("/{id}/wppwcts", name="app_api_projects_wppwcts")
     * @Method({"GET"})
     *
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function wppwctsAction(Project $project)
    {
        $wppwcts = $this
            ->getDoctrine()
            ->getRepository(WorkPackageProjectWorkCostType::class)
            ->findByProject($project)
        ;

        return $this->createApiResponse($wppwcts);
    }

    /**
     * Retrieve calendar files for users.
     *
     * @Route("/{id}/export-calendars", name="app_api_users_export_calendars", options={"expose"=true})
     * @Method({"GET"})
     *
     * @param Request $request
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function exportCalendarsAction(Request $request, Project $project)
    {
        if (!$this->getUser()) {
            return $this->createApiResponse([
                'message' => $this
                    ->get('translator')
                    ->trans('not_found.general', [], 'messages'),
            ], Response::HTTP_NOT_FOUND);
        }

        $filters = $request->query->all();
        $filters['project'] = $project->getName();

        return $this->get('app.service.calendar')->exportUserCalendars($filters, $this->getUser());
    }

    /**
     * All project objectives for a specific Project.
     *
     * @Route("/{id}/objectives", name="app_api_project_objectives", options={"expose"=true})
     * @Method({"GET"})
     *
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function objectivesAction(Project $project)
    {
        return $this->createApiResponse($project->getProjectObjectives());
    }

    /**
     * Create a new ProjectObjective.
     *
     * @Route("/{id}/objectives", name="app_api_project_create_objective", options={"expose"=true})
     * @Method({"POST"})
     *
     * @param Request $request
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function createObjectiveAction(Request $request, Project $project)
    {
        $form = $this->createForm(ProjectObjectiveCreateType::class, new ProjectObjective(), ['csrf_protection' => false]);
        $this->processForm($request, $form);

        if ($form->isValid()) {
            $projectObjective = $form->getData();
            $projectObjective->setProject($project);
            $em = $this->getDoctrine()->getManager();
            $em->persist($projectObjective);
            $em->flush();

            return $this->createApiResponse($projectObjective, Response::HTTP_CREATED);
        }

        $errors = $this->getFormErrors($form);
        $errors = [
            'messages' => $errors,
        ];

        return $this->createApiResponse($errors, Response::HTTP_BAD_REQUEST);
    }

    /**
     * All project limitations for a specific Project.
     *
     * @Route("/{id}/limitations", name="app_api_project_limitations", options={"expose"=true})
     * @Method({"GET"})
     *
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function limitationsAction(Project $project)
    {
        return $this->createApiResponse($project->getProjectLimitations());
    }

    /**
     * Create a new ProjectLimitation.
     *
     * @Route("/{id}/limitations", name="app_api_project_create_limitation", options={"expose"=true})
     * @Method({"POST"})
     *
     * @param Request $request
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function createLimitationAction(Request $request, Project $project)
    {
        $form = $this->createForm(ProjectLimitationCreateType::class, new ProjectLimitation(), ['csrf_protection' => false]);
        $this->processForm($request, $form);

        if ($form->isValid()) {
            $projectLimitation = $form->getData();
            $projectLimitation->setProject($project);
            $em = $this->getDoctrine()->getManager();
            $em->persist($projectLimitation);
            $em->flush();

            return $this->createApiResponse($projectLimitation, Response::HTTP_CREATED);
        }

        $errors = $this->getFormErrors($form);
        $errors = [
            'messages' => $errors,
        ];

        return $this->createApiResponse($errors, Response::HTTP_BAD_REQUEST);
    }

    /**
     * All project deliverables for a specific Project.
     *
     * @Route("/{id}/deliverables", name="app_api_project_deliverables", options={"expose"=true})
     * @Method({"GET"})
     *
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function deliverablesAction(Project $project)
    {
        return $this->createApiResponse($project->getProjectDeliverables());
    }

    /**
     * Create a new ProjectDeliverable.
     *
     * @Route("/{id}/deliverables", name="app_api_project_create_deliverable", options={"expose"=true})
     * @Method({"POST"})
     *
     * @param Request $request
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function createDeliverableAction(Request $request, Project $project)
    {
        $form = $this->createForm(ProjectDeliverableCreateType::class, new ProjectDeliverable(), ['csrf_protection' => false]);
        $this->processForm($request, $form);

        if ($form->isValid()) {
            $projectDeliverable = $form->getData();
            $projectDeliverable->setProject($project);
            $em = $this->getDoctrine()->getManager();
            $em->persist($projectDeliverable);
            $em->flush();

            return $this->createApiResponse($projectDeliverable, Response::HTTP_CREATED);
        }

        $errors = $this->getFormErrors($form);
        $errors = [
            'messages' => $errors,
        ];

        return $this->createApiResponse($errors, Response::HTTP_BAD_REQUEST);
    }

    /**
     * All project resources costs.
     *
     * @Route("/{id}/resources", name="app_api_project_resources", options={"expose"=true})
     * @Method({"GET"})
     *
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function resourcesAction(Project $project)
    {
        // @TODO: replace with \AppBundle\Entity\Resource AND \AppBundle\Entity\Cost
        return $this->createApiResponse(
            [
                'internal' => $this
                    ->getDoctrine()
                    ->getRepository(WorkPackageProjectWorkCostType::class)
                    ->costsByProject($project),
                'external' => $this
                    ->getDoctrine()
                    ->getRepository(WorkPackageProjectWorkCostType::class)
                    ->costsByProject($project, false),
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/{id}/phases", name="app_api_project_phases", options={"expose"=true})
     */
    public function phasesAction(Project $project)
    {
        $phases = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository(WorkPackage::class)
            ->findPhasesByProject($project)
        ;

        return $this->createApiResponse($phases);
    }

    /**
     * @Route("/{id}/milestones", name="app_api_project_milestones", options={"expose"=true})
     */
    public function milestonesAction(Request $request, Project $project)
    {
        $repo = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository(WorkPackage::class)
        ;

        $filters = $request->query->get('filters', []);

        if (isset($filters['phase'])) {
            $phase = $repo->findOneBy([
                'id' => $filters['phase'],
                'type' => WorkPackage::TYPE_PHASE,
            ]);

            if (!$phase) {
                throw $this->createNotFoundException();
            }

            return $this->createApiResponse($repo->findMilestonesByPhase($phase));
        }

        return $this->createApiResponse($repo->findMilestonesByProject($project));
    }

    /**
     * @Route("/{id}/tasks", name="app_api_project_tasks", options={"expose"=true})
     * @Method({"GET"})
     */
    public function tasksAction(Request $request, Project $project)
    {
        $repo = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository(WorkPackage::class);

        $filters = $request->query->get('filters', []);

        if (isset($filters['milestone'])) {
            $milestone = $repo->findOneBy([
                'id' => $filters['milestone'],
                'type' => WorkPackage::TYPE_MILESTONE,
            ]);

            if (!$milestone) {
                throw $this->createNotFoundException();
            }

            return $this->createApiResponse($repo->findTasksByMilestone($milestone));
        }

        return $this->createApiResponse($repo->findTasksByProject($project));
    }

    /**
     * Create a new WorkPackage.
     *
     * @Route("/{id}/tasks", name="app_api_project_tasks_create", options={"expose"=true})
     * @Method({"POST"})
     *
     * @param Request $request
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function createTaskAction(Request $request, Project $project)
    {
        $form = $this->createForm(ApiCreateType::class, new WorkPackage());
        $this->processForm($request, $form);

        if ($form->isValid()) {
            $wp = $form->getData();
            $wp->setProject($project);
            $this->persistAndFlush($wp);

            return $this->createApiResponse($wp, Response::HTTP_CREATED);
        }

        $errors = $this->getFormErrors($form);
        $errors = [
            'messages' => $errors,
        ];

        return $this->createApiResponse($errors, Response::HTTP_BAD_REQUEST);
    }

    /**
     * All project units.
     *
     * @Route("/{id}/units", name="app_api_project_units", options={"expose"=true})
     * @Method({"GET"})
     *
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function unitsAction(Project $project)
    {
        return $this->createApiResponse($project->getUnits());
    }

    /**
     * Create a new Unit.
     *
     * @Route("/{id}/units", name="app_api_project_create_unit", options={"expose"=true})
     * @Method({"POST"})
     *
     * @param Request $request
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function createUnitAction(Request $request, Project $project)
    {
        $form = $this->createForm(UnitCreateType::class, new Unit());
        $this->processForm($request, $form);

        if ($form->isValid()) {
            $unit = $form->getData();
            $unit->setProject($project);
            $this->persistAndFlush($unit);

            return $this->createApiResponse($unit, Response::HTTP_CREATED);
        }

        $errors = $this->getFormErrors($form);
        $errors = [
            'messages' => $errors,
        ];

        return $this->createApiResponse($errors, Response::HTTP_BAD_REQUEST);
    }

    /**
     * All project work packages.
     *
     * @Route("/{id}/work-packages", name="app_api_project_work_packages", options={"expose"=true})
     * @Method({"GET"})
     *
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function workPackagesAction(Project $project)
    {
        $workPackages = $this
            ->getDoctrine()
            ->getRepository(WorkPackage::class)
            ->findByProject($project)
        ;

        return $this->createApiResponse($workPackages);
    }
}
