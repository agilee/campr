<?php

namespace MainBundle\Controller;

use AppBundle\Entity\Team;
use AppBundle\Entity\TeamInvite;
use AppBundle\Entity\TeamMember;
use AppBundle\Entity\User;
use Component\Team\TeamEvents;
use MainBundle\Form\Team\CreateType;
use MainBundle\Form\Team\EditType;
use MainBundle\Form\TeamMember\EditRolesType;
use MainBundle\Security\TeamVoter;
use MainBundle\Form\InviteUserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Team controller.
 *
 * @Route("/team")
 */
class TeamController extends Controller
{
    /**
     * Team list.
     *
     * @Route("/list", name="main_team_list")
     * @Method("GET")
     *
     * @return Response
     */
    public function listAction()
    {
        $teams = $this->getUser()->getTeams()->toArray();
        $teamsAsMember = $this
            ->getDoctrine()
            ->getRepository(Team::class)
            ->findByTeamMember($this->getUser())
        ;
        $teams = array_merge($teams, $teamsAsMember);
        $teams = array_unique($teams);

        return $this->render(
            'MainBundle:Team:list.html.twig',
            [
                'teams' => $teams,
            ]
        );
    }

    /**
     * Create new team.
     *
     * @Route("/create", name="main_team_create")
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        // softdeleteable is incompatible with UniqueEntity validator
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');

        $team = new Team();
        $form = $this->createForm(CreateType::class, $team);
        $form->handleRequest($request);

        if ($request->isMethod(Request::METHOD_POST) && $form->isValid()) {
            $team = $form->getData();
            $team->setUser($this->getUser());

            $eventDispatcher = $this->get('event_dispatcher');
            $eventDispatcher->dispatch(TeamEvents::PRE_CREATE, new GenericEvent($team));

            $teamMember = new TeamMember();
            $teamMember->setTeam($team);
            $teamMember->setUser($this->getUser());
            $teamMember->setRoles([User::ROLE_SUPER_ADMIN]);

            $em->persist($team);
            $em->persist($teamMember);
            $em->flush();

            $eventDispatcher->dispatch(TeamEvents::POST_CREATE, new GenericEvent($team));

            $this
                ->get('session')
                ->getFlashBag()
                ->set(
                    'success',
                    $this
                        ->get('translator')
                        ->trans('success.workspace.create', [], 'flashes')
                )
            ;

            return $this->redirectToRoute('main_team_show', ['id' => $team->getId()]);
        }

        return $this->render('MainBundle:Team:create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Edit a specific team.
     *
     * @Route("/{id}/edit", name="main_team_edit")
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     * @param Team    $team
     *
     * @return Response
     */
    public function editAction(Request $request, Team $team)
    {
        $this->denyAccessUnlessGranted(TeamVoter::EDIT, $team);

        // softdeleteable is incompatible with UniqueEntity validator
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');

        $form = $this->createForm(EditType::class, $team);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isValid()) {
            $team = $form->getData();

            $em->persist($team);
            $em->flush();

            $this
                ->get('session')
                ->getFlashBag()
                ->set(
                    'success',
                    $this
                        ->get('translator')
                        ->trans('success.workspace.edit', [], 'flashes')
                )
            ;

            return $this->redirectToRoute('main_team_show', ['id' => $team->getId()]);
        }

        return $this->render(
            'MainBundle:Team:edit.html.twig',
            [
                'team' => $team,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Display Team entity.
     *
     * @Route("/{id}/show", name="main_team_show")
     * @Method({"GET"})
     *
     * @param Team $team
     *
     * @return Response
     */
    public function showAction(Team $team)
    {
        $em = $this->getDoctrine()->getManager();
        $currentTeamMember = $em
            ->getRepository(TeamMember::class)
            ->findOneBy([
                'user' => $this->getUser(),
                'team' => $team,
            ])
        ;

        return $this->render(
            'MainBundle:Team:show.html.twig',
            [
                'team' => $team,
                'current_team_member' => $currentTeamMember,
            ]
        );
    }

    /**
     * @Route("/{id}/sso", name="main_team_sso")
     */
    public function ssoAction(Request $request, Team $team)
    {
        if (!$team->isEnabled()) {
            throw $this->createNotFoundException('Team is disabled.');
        }

        /** @var User $user */
        $user = $this->getUser();
        $subdomain = sprintf('%s://%s.%s', $request->getScheme(), $team->getSlug(), $request->getHost());

        if ($team->getUser() !== $user) {
            $teamMember = $team->getTeamMemberForUser($user);
            $roles = $teamMember ? $teamMember->getRoles() : [];

            if (!count($roles)) {
                throw $this->createAccessDeniedException();
            }
        } else {
            $roles = [User::ROLE_SUPER_ADMIN];
        }

        $userData = [
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'phone' => $user->getPhone(),
            'roles' => $roles,
            'apiToken' => $user->getApiToken(),
            'widgetSettings' => $user->getWidgetSettings(),
            'avatarUrl' => $user->getAvatarUrl(),
            'facebook' => $user->getFacebook(),
            'twitter' => $user->getTwitter(),
            'instagram' => $user->getInstagram(),
            'gplus' => $user->getGplus(),
            'linkedIn' => $user->getLinkedIn(),
            'medium' => $user->getMedium(),
            'locale' => $user->getLocale(),
            'uuid' => $user->getUuid(),
            'theme' => $user->getTheme(),
        ];

        $redirectTo = $request->query->get('redirect_to');
        if (!empty($redirectTo)) {
            $userData['redirect_to'] = $redirectTo;
        }

        $signer = $this->get('app.jwt_signer');
        $token = $this->get('app.jwt_builder')
            ->setIssuer(sprintf('%s://%s', $request->getScheme(), $request->getHost()))
            ->setIssuedAt(time())
            ->setExpiration(time() + 3)
            ->setId(uniqid())
            ->set('user', $userData)
            ->sign($signer, $this->getParameter('secret'))
            ->getToken()
        ;

        $path = $this->get('router')->generate('portal_login', [
            'jwt' => (string) $token,
        ]);

        return $this->redirect($subdomain.$path);
    }

    /**
     * Deletes a specific Team entity.
     *
     * @Route("/{id}/delete", name="main_team_delete")
     * @Method({"POST"})
     *
     * @param Team $team
     *
     * @return Response
     */
    public function deleteAction(Team $team)
    {
        $this->denyAccessUnlessGranted(TeamVoter::DELETE, $team);

        $em = $this->getDoctrine()->getManager();
        $em->remove($team);
        $em->flush();

        $this
            ->get('session')
            ->getFlashBag()
            ->set(
                'success',
                $this
                    ->get('translator')
                    ->trans('success.workspace.delete', [], 'flashes')
            );

        return $this->redirectToRoute('main_team_list');
    }

    /**
     * Invite one user to be part of a team.
     *
     * @Route("/{id}/invite-user", name="main_team_invite_user")
     *
     * @param Request $request
     * @param Team    $team
     *
     * @return Response
     */
    public function inviteUserAction(Request $request, Team $team)
    {
        $this->denyAccessUnlessGranted(TeamVoter::INVITE, $team);

        $form = $this->createForm(
            InviteUserType::class,
            null,
            [
                'team' => $team,
                'user' => $this->getUser(),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $em = $this->getDoctrine()->getManager();
            $user = $em
                ->getRepository(User::class)
                ->findOneBy([
                    'email' => $email,
                ])
            ;

            // check for already existing invite
            $teamInvite = $em
                ->getRepository(TeamInvite::class)
                ->findOneByEmailAndTeam($email, $team)
            ;

            if (!$teamInvite) {
                $teamInvite = new TeamInvite();
                $teamInvite->setTeam($team);
            }

            // if there's a team invite by email but the user registered in the mean time
            // this will associate the invite with the existing user
            if ($user) {
                $teamInvite->setUser($user);
            } else {
                $teamInvite->setEmail($email);
            }

            $token = uniqid(rand(1000, 9999), true);
            $teamInvite->setToken($token);

            $em->persist($teamInvite);
            $em->flush();

            $mailerService = $this->get('app.service.mailer');
            $mailerService->sendEmail(
                'MainBundle:Email:invite_user.html.twig',
                'notification',
                $email,
                [
                    'token' => $token,
                    'user_email' => $email,
                    'team' => $team,
                ]
            );

            $message = $this
                ->get('translator')
                ->trans('success.workspace_member.invite', ['%user_email%' => $email], 'flashes')
            ;
            $this
                ->get('session')
                ->getFlashBag()
                ->set('success', $message)
            ;

            return $this->redirectToRoute('main_team_invite_user', ['id' => $team->getId()]);
        }

        return $this->render(
            'MainBundle:Team:invite_user.html.twig',
            [
                'form' => $form->createView(),
                'team' => $team,
            ]
        );
    }

    /**
     * Accepts invitation to a new team.
     *
     * @Route("/invitation-accepted/{token}", name="main_team_invitation_accepted")
     *
     * @param string $token
     *
     * @return Response
     */
    public function invitationAcceptedAction($token)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var TeamInvite $teamInvite */
        $teamInvite = $em
            ->getRepository(TeamInvite::class)
            ->findOneBy([
                'token' => $token,
            ])
        ;

        if (!$teamInvite) {
            throw $this->createNotFoundException();
        }

        if ($teamInvite->getAcceptedAt()) {
            $message = $this
                ->get('translator')
                ->trans(
                    'success.workspace_member.invitation.already_accepted',
                    [
                        '%team_name%' => $teamInvite->getTeam()->getName(),
                    ],
                    'flashes'
                )
            ;
        } else {
            $user = null;
            $newUser = false;

            if ($teamInvite->getEmail()) {
                $user = $em
                    ->getRepository(User::class)
                    ->findOneBy(['email' => $teamInvite->getEmail()])
                ;
            } elseif ($teamInvite->getUser()) {
                $user = $teamInvite->getUser();
            }

            if (!$user) {
                $user = new User();
                $user->setFirstName('new');
                $user->setLastName('user');
                $user->setEmail($teamInvite->getEmail());
                $user->setRoles([User::ROLE_USER]);
                $user->setPlainPassword(substr(md5(microtime()), rand(0, 26), 6));
                $em->persist($user);
                $newUser = true;
            }

            if ($user->getId() && $teamInvite->getTeam()->userIsMember($user)) {
                $message = $this
                    ->get('translator')
                    ->trans(
                        'invite.team_member',
                        [
                            '%team_name%' => $teamInvite->getTeam()->getName(),
                        ],
                        'validators'
                    )
                ;

                return $this->render(
                    'MainBundle:Team:invitation_accepted.html.twig',
                    [
                        'message' => $message,
                    ]
                );
            }

            $teamMember = (new TeamMember())
                ->setUser($user)
                ->setTeam($teamInvite->getTeam())
                ->setRoles([User::ROLE_USER])
            ;
            $em->persist($teamMember);

            $teamInvite->setUser($user);
            $teamInvite->setAcceptedAt(new \DateTime());
            $em->persist($teamInvite);

            $em->flush();

            if ($newUser) {
                $this
                    ->get('app.service.mailer')
                    ->sendRegistrationEmail($user)
                ;
            }

            $message = $this
                ->get('translator')
                ->trans(
                    'success.workspace_member.invitation.new_user',
                    [
                        '%team_name%' => $teamInvite->getTeam()->getName(),
                    ],
                    'flashes'
                )
            ;

            return $this->render(
                'MainBundle:Team:invitation_accepted.html.twig',
                [
                    'message' => $message,
                ]
            );
        }

        return $this->render(
            'MainBundle:Team:invitation_accepted.html.twig',
            [
                'message' => $message,
            ]
        );
    }

    /**
     * TODO: Add Dan's voter to deny access if you are not part of the team and if you are not team owner.
     *
     * Remove user from a team.
     *
     * @Route("/{team}/member/{id}/remove", name="main_team_remove_team_member")
     *
     * @param Team       $team
     * @param TeamMember $teamMember
     *
     * @return Response|RedirectResponse
     */
    public function removeTeamMemberAction(Team $team, TeamMember $teamMember)
    {
        if ($team !== $teamMember->getTeam()) {
            $message = $this
                ->get('translator')
                ->trans(
                    'failed.workspace_member.remove.not_part_of_the_team',
                    [
                        '%team_member_name%' => $teamMember->getUser()->getUsername(),
                        '%team_name%' => $team->getName(),
                    ],
                    'flashes'
                )
            ;
            $this
                ->get('session')
                ->getFlashBag()
                ->set('error', $message)
            ;

            return $this->redirectToRoute('main_team_show', ['id' => $team->getId()]);
        }

        if ($this->getUser() === $teamMember->getUser()) {
            $message = $this
                ->get('translator')
                ->trans(
                    'failed.workspace_member.remove.yourself',
                    [
                        '%team_name%' => $team->getName(),
                    ],
                    'flashes'
                )
            ;
            $this
                ->get('session')
                ->getFlashBag()
                ->set('error', $message)
            ;

            return $this->redirectToRoute('main_team_show', ['id' => $team->getId()]);
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($teamMember);
        $em->flush();

        $message = $this
            ->get('translator')
            ->trans(
                'success.workspace_member.remove',
                [
                    '%team_member_name%' => $teamMember->getUser()->getUsername(),
                    '%team_name%' => $team->getName(),
                ],
                'flashes'
            )
        ;
        $this
            ->get('session')
            ->getFlashBag()
            ->set('success', $message)
        ;

        return $this->redirectToRoute('main_team_show', ['id' => $team->getId()]);
    }

    /**
     * @Route("/{team}/edit-team-member/{teamMember}", name="main_team_edit_team_member")
     * @Method({"GET", "POST"})
     */
    public function editTeamMemberAction(Request $request, Team $team, TeamMember $teamMember)
    {
        if ($teamMember->getUser() === $team->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(EditRolesType::class, $teamMember, [
            'method' => $request->getMethod(),
        ]);

        if ($request->isMethod(Request::METHOD_POST)) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($teamMember);
                $em->flush();

                return $this->redirectToRoute('main_team_show', [
                    'id' => $team->getId(),
                ]);
            }
        }

        return $this->render('MainBundle:Team:edit_team_member_roles.html.twig', [
            'team' => $team,
            'teamMember' => $teamMember,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/check-availability", name="main_team_check_availability", options={"expose": true})
     * @Method("GET")
     */
    public function checkTeamAvailabilityAction(Team $team)
    {
        return new JsonResponse([
            'available' => $team->isEnabled() && $team->isAvailable(),
        ]);
    }
}
