<?php

namespace AppBundle\Tests\Controller\API;

use AppBundle\Entity\Contract;
use AppBundle\Entity\DistributionList;
use AppBundle\Entity\Project;
use AppBundle\Entity\ProjectTeam;
use AppBundle\Entity\ProjectUser;
use MainBundle\Tests\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;

class ProjectControllerTest extends BaseController
{
    /**
     * @dataProvider getDataForCreateAction()
     *
     * @param array $content
     * @param $isResponseSuccessful
     * @param $responseStatusCode
     * @param $responseContent
     */
    public function testCreateAction(
        array $content,
        $isResponseSuccessful,
        $responseStatusCode,
        $responseContent
    ) {
        $user = $this->getUserByUsername('superadmin');
        $token = $user->getApiToken();

        $this->client->request(
            'POST',
            '/api/projects',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            json_encode($content)
        );
        $response = $this->client->getResponse();

        $project = json_decode($response->getContent(), true);
        $responseContent['createdAt'] = $project['createdAt'];
        $responseContent['updatedAt'] = $project['updatedAt'];

        $this->assertEquals($isResponseSuccessful, $response->isSuccessful());
        $this->assertEquals($responseStatusCode, $response->getStatusCode());
        $this->assertEquals(json_encode($responseContent), $response->getContent());

        $project = $this
            ->em
            ->getRepository(Project::class)
            ->find($project['id'])
        ;
        $this->em->remove($project);
        $this->em->flush();
    }

    /**
     * @return array
     */
    public function getDataForCreateAction()
    {
        return [
            [
                [
                    'name' => 'project3',
                    'number' => 'project-number-3',
                ],
                true,
                Response::HTTP_CREATED,
                [
                    'company' => null,
                    'companyName' => null,
                    'projectComplexity' => null,
                    'projectComplexityName' => null,
                    'projectCategory' => null,
                    'projectCategoryName' => null,
                    'projectScope' => null,
                    'projectScopeName' => null,
                    'status' => 1,
                    'statusName' => 'project-status1',
                    'portfolio' => null,
                    'portfolioName' => null,
                    'userFavorites' => [],
                    'id' => 3,
                    'name' => 'project3',
                    'number' => 'project-number-3',
                    'projectUsers' => [],
                    'projectTeams' => [],
                    'notes' => [],
                    'todos' => [],
                    'distributionLists' => [],
                    'statusUpdatedAt' => null,
                    'approvedAt' => null,
                    'createdAt' => '',
                    'updatedAt' => null,
                    'contracts' => [],
                    'logo' => null,
                ],
            ],
        ];
    }

    /**
     * @dataProvider getDataForNumberIsUniqueOnCreateAction
     *
     * @param array $content
     * @param $isResponseSuccessful
     * @param $responseStatusCode
     * @param $responseContent
     */
    public function testNumberIsUniqueOnCreateAction(
        array $content,
        $isResponseSuccessful,
        $responseStatusCode,
        $responseContent
    ) {
        $project = (new Project())
            ->setName('project3')
            ->setNumber('project-number-3')
        ;
        $this->em->persist($project);
        $this->em->flush();

        $user = $this->getUserByUsername('superadmin');
        $token = $user->getApiToken();

        $this->client->request(
            'POST',
            '/api/projects',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            json_encode($content)
        );
        $response = $this->client->getResponse();

        $this->assertEquals($isResponseSuccessful, $response->isClientError());
        $this->assertEquals($responseStatusCode, $response->getStatusCode());
        $this->assertEquals(json_encode($responseContent), $response->getContent());

        $this->em->remove($project);
        $this->em->flush();
    }

    /**
     * @return array
     */
    public function getDataForNumberIsUniqueOnCreateAction()
    {
        return [
            [
                [
                    'name' => 'project3',
                    'number' => 'project-number-3',
                ],
                true,
                Response::HTTP_BAD_REQUEST,
                [
                    'messages' => [
                        'number' => ['That number is taken'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getDataForFieldsNotBlankOnCreateAction
     *
     * @param array $content
     * @param $isResponseSuccessful
     * @param $responseStatusCode
     * @param $responseContent
     */
    public function testFieldsNotBlankOnCreateAction(
        array $content,
        $isResponseSuccessful,
        $responseStatusCode,
        $responseContent
    ) {
        $user = $this->getUserByUsername('superadmin');
        $token = $user->getApiToken();

        $this->client->request(
            'POST',
            '/api/projects',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            json_encode($content)
        );
        $response = $this->client->getResponse();

        $this->assertEquals($isResponseSuccessful, $response->isClientError());
        $this->assertEquals($responseStatusCode, $response->getStatusCode());
        $this->assertEquals(json_encode($responseContent), $response->getContent());
    }

    /**
     * @return array
     */
    public function getDataForFieldsNotBlankOnCreateAction()
    {
        return [
            [
                [],
                true,
                Response::HTTP_BAD_REQUEST,
                [
                    'messages' => [
                        'name' => ['The name field should not be blank'],
                        'number' => ['The number field should not be blank'],
                    ],

                ],
            ],
        ];
    }

    /**
     * @dataProvider getDataForEditAction
     *
     * @param array $content
     * @param $isResponseSuccessful
     * @param $responseStatusCode
     * @param $responseContent
     */
    public function testEditAction(
        array $content,
        $isResponseSuccessful,
        $responseStatusCode,
        $responseContent
    ) {
        $user = $this->getUserByUsername('superadmin');
        $token = $user->getApiToken();

        $this->client->request(
            'PATCH',
            '/api/projects/1',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            json_encode($content)
        );
        $response = $this->client->getResponse();

        $project = json_decode($response->getContent(), true);
        $responseContent['updatedAt'] = $project['updatedAt'];
        $responseContent['updatedAt'] = $project['updatedAt'];
        $responseContent['projectTeams'][0]['updatedAt'] = $project['projectTeams'][0]['updatedAt'];
        $responseContent['projectTeams'][1]['updatedAt'] = $project['projectTeams'][1]['updatedAt'];
        $responseContent['distributionLists'][0]['updatedAt'] = $project['distributionLists'][0]['updatedAt'];
        $responseContent['distributionLists'][1]['updatedAt'] = $project['distributionLists'][1]['updatedAt'];
        $responseContent['distributionLists'][0]['users'][0]['apiToken'] = $project['distributionLists'][0]['users'][0]['apiToken'];
        $responseContent['distributionLists'][1]['users'][0]['apiToken'] = $project['distributionLists'][1]['users'][0]['apiToken'];
        $responseContent['distributionLists'][0]['users'][0]['updatedAt'] = $project['distributionLists'][0]['users'][0]['updatedAt'];
        $responseContent['distributionLists'][1]['users'][0]['updatedAt'] = $project['distributionLists'][1]['users'][0]['updatedAt'];
        $responseContent['contracts'][0]['updatedAt'] = $project['contracts'][0]['updatedAt'];
        $responseContent['contracts'][1]['updatedAt'] = $project['contracts'][1]['updatedAt'];

        for ($i = 1; $i <= 3; ++$i) {
            $projectUser = $this->em->getRepository(ProjectUser::class)->find($i);
            $responseContent['projectUsers'][$i - 1]['updatedAt'] = $projectUser->getUpdatedAt()->format('Y-m-d H:i:s');
        }

        $this->assertEquals($isResponseSuccessful, $response->isSuccessful());
        $this->assertEquals($responseStatusCode, $response->getStatusCode());
        $this->assertEquals(json_encode($responseContent), $response->getContent());
    }

    /**
     * @return array
     */
    public function getDataForEditAction()
    {
        return [
            [
                [
                    'name' => 'project1',
                ],
                true,
                Response::HTTP_ACCEPTED,
                [
                    'company' => null,
                    'companyName' => null,
                    'projectComplexity' => 1,
                    'projectComplexityName' => 'project-complexity1',
                    'projectCategory' => 1,
                    'projectCategoryName' => 'project-category1',
                    'projectScope' => 1,
                    'projectScopeName' => 'project-scope1',
                    'status' => 1,
                    'statusName' => 'project-status1',
                    'portfolio' => 1,
                    'portfolioName' => 'portfolio1',
                    'userFavorites' => [],
                    'id' => 1,
                    'name' => 'project1',
                    'number' => 'project-number-1',
                    'projectUsers' => [
                        [
                            'user' => 3,
                            'userFullName' => 'FirstName3 LastName3',
                            'project' => 1,
                            'projectName' => 'project1',
                            'projectCategory' => 1,
                            'projectCategoryName' => 'project-category1',
                            'projectRole' => 1,
                            'projectRoleName' => 'manager',
                            'projectDepartment' => 1,
                            'projectDepartmentName' => 'project-department1',
                            'projectTeam' => 1,
                            'projectTeamName' => 'project-team1',
                            'id' => 1,
                            'showInResources' => true,
                            'showInRaci' => null,
                            'showInOrg' => null,
                            'createdAt' => '2017-01-01 12:00:00',
                            'updatedAt' => null,
                        ],
                        [
                            'user' => 4,
                            'userFullName' => 'FirstName4 LastName4',
                            'project' => 1,
                            'projectName' => 'project1',
                            'projectCategory' => 2,
                            'projectCategoryName' => 'project-category2',
                            'projectRole' => 2,
                            'projectRoleName' => 'sponsor',
                            'projectDepartment' => 2,
                            'projectDepartmentName' => 'project-department2',
                            'projectTeam' => 2,
                            'projectTeamName' => 'project-team2',
                            'id' => 2,
                            'showInResources' => true,
                            'showInRaci' => null,
                            'showInOrg' => null,
                            'createdAt' => '2017-01-01 12:00:00',
                            'updatedAt' => null,
                        ],
                        [
                            'user' => 5,
                            'userFullName' => 'FirstName5 LastName5',
                            'project' => 1,
                            'projectName' => 'project1',
                            'projectCategory' => 1,
                            'projectCategoryName' => 'project-category1',
                            'projectRole' => 3,
                            'projectRoleName' => 'team-member',
                            'projectDepartment' => 1,
                            'projectDepartmentName' => 'project-department1',
                            'projectTeam' => 1,
                            'projectTeamName' => 'project-team1',
                            'id' => 3,
                            'showInResources' => true,
                            'showInRaci' => null,
                            'showInOrg' => null,
                            'createdAt' => '2017-01-01 12:00:00',
                            'updatedAt' => null,
                        ],
                    ],
                    'projectTeams' => [
                        [
                            'project' => 1,
                            'projectName' => 'project1',
                            'parent' => null,
                            'parentName' => null,
                            'id' => 1,
                            'name' => 'project-team1',
                            'createdAt' => '2017-01-01 12:00:00',
                            'updatedAt' => null,
                            'children' => [],
                        ],
                        [
                            'project' => 1,
                            'projectName' => 'project1',
                            'parent' => null,
                            'parentName' => null,
                            'id' => 2,
                            'name' => 'project-team2',
                            'createdAt' => '2017-01-01 12:00:00',
                            'updatedAt' => null,
                            'children' => [],
                        ],
                    ],
                    'notes' => [
                        [
                            'status' => 1,
                            'statusName' => 'status1',
                            'meeting' => 1,
                            'meetingName' => 'meeting1',
                            'project' => 1,
                            'projectName' => 'project1',
                            'responsibility' => 4,
                            'responsibilityFullName' => 'FirstName4 LastName4',
                            'id' => 1,
                            'title' => 'note1',
                            'description' => 'description1',
                            'showInStatusReport' => false,
                            'date' => '2017-01-01 00:00:00',
                            'dueDate' => '2017-05-01 00:00:00',
                        ],
                        [
                            'status' => 1,
                            'statusName' => 'status1',
                            'meeting' => 1,
                            'meetingName' => 'meeting1',
                            'project' => 1,
                            'projectName' => 'project1',
                            'responsibility' => 4,
                            'responsibilityFullName' => 'FirstName4 LastName4',
                            'id' => 2,
                            'title' => 'note2',
                            'description' => 'description2',
                            'showInStatusReport' => false,
                            'date' => '2017-01-01 00:00:00',
                            'dueDate' => '2017-05-01 00:00:00',
                        ],
                    ],
                    'todos' => [
                        [

                            'status' => 1,
                            'statusName' => 'status1',
                            'meeting' => 1,
                            'meetingName' => 'meeting1',
                            'project' => 1,
                            'projectName' => 'project1',
                            'responsibility' => 4,
                            'responsibilityFullName' => 'FirstName4 LastName4',
                            'id' => 1,
                            'title' => 'todo1',
                            'description' => 'description for todo1',
                            'showInStatusReport' => false,
                            'date' => '2017-01-01 00:00:00',
                            'dueDate' => '2017-05-01 00:00:00',
                        ],
                        [
                            'status' => 1,
                            'statusName' => 'status1',
                            'meeting' => 1,
                            'meetingName' => 'meeting1',
                            'project' => 1,
                            'projectName' => 'project1',
                            'responsibility' => 4,
                            'responsibilityFullName' => 'FirstName4 LastName4',
                            'id' => 2,
                            'title' => 'todo2',
                            'description' => 'description for todo2',
                            'showInStatusReport' => false,
                            'date' => '2017-01-01 00:00:00',
                            'dueDate' => '2017-05-01 00:00:00',
                        ],
                    ],
                    'distributionLists' => [
                        [
                            'project' => 1,
                            'projectName' => 'project1',
                            'createdBy' => 1,
                            'createdByFullName' => 'FirstName1 LastName1',
                            'id' => 1,
                            'name' => 'distribution-list-1',
                            'sequence' => 1,
                            'users' => [
                                [
                                    'roles' => ['ROLE_USER'],
                                    'id' => 7,
                                    'username' => 'user10',
                                    'email' => 'user10@trisoft.ro',
                                    'phone' => null,
                                    'firstName' => 'FirstName10',
                                    'lastName' => 'LastName10',
                                    'isEnabled' => true,
                                    'isSuspended' => false,
                                    'createdAt' => '2017-01-01 00:00:00',
                                    'updatedAt' => null,
                                    'activatedAt' => null,
                                    'teams' => [],
                                    'apiToken' => null,
                                    'widgetSettings' => [],
                                    'facebook' => null,
                                    'twitter' => null,
                                    'instagram' => null,
                                    'gplus' => null,
                                    'linkedIn' => null,
                                    'medium' => null,
                                    'ownedDistributionLists' => [],
                                    'contracts' => [],
                                    'ownedMeetings' => [],
                                    'avatar' => null,
                                ],
                            ],
                            'meetings' => [],
                            'createdAt' => '2017-01-01 07:00:00',
                            'updatedAt' => '2017-01-30 07:11:12',
                        ],
                        [
                            'project' => 1,
                            'projectName' => 'project1',
                            'createdBy' => 1,
                            'createdByFullName' => 'FirstName1 LastName1',
                            'id' => 2,
                            'name' => 'distribution-list-2',
                            'sequence' => 1,
                            'users' => [
                                [
                                    'roles' => ['ROLE_USER'],
                                    'id' => 7,
                                    'username' => 'user10',
                                    'email' => 'user10@trisoft.ro',
                                    'phone' => null,
                                    'firstName' => 'FirstName10',
                                    'lastName' => 'LastName10',
                                    'isEnabled' => true,
                                    'isSuspended' => false,
                                    'createdAt' => '2017-01-01 00:00:00',
                                    'updatedAt' => null,
                                    'activatedAt' => null,
                                    'teams' => [],
                                    'apiToken' => null,
                                    'widgetSettings' => [],
                                    'facebook' => null,
                                    'twitter' => null,
                                    'instagram' => null,
                                    'gplus' => null,
                                    'linkedIn' => null,
                                    'medium' => null,
                                    'ownedDistributionLists' => [],
                                    'contracts' => [],
                                    'ownedMeetings' => [],
                                    'avatar' => null,
                                ],
                            ],
                            'meetings' => [],
                            'createdAt' => '2017-01-01 07:00:00',
                            'updatedAt' => '2017-01-30 07:11:12',
                        ],
                    ],
                    'statusUpdatedAt' => null,
                    'approvedAt' => null,
                    'createdAt' => '2017-01-01 12:00:00',
                    'updatedAt' => null,
                    'contracts' => [
                        [
                            'project' => 1,
                            'projectName' => 'project1',
                            'createdBy' => 1,
                            'createdByFullName' => 'FirstName1 LastName1',
                            'id' => 1,
                            'name' => 'contract1',
                            'description' => 'contract-description1',
                            'proposedStartDate' => '2017-01-01',
                            'proposedEndDate' => '2017-05-01',
                            'forecastStartDate' => null,
                            'forecastEndDate' => null,
                            'createdAt' => '2017-01-01 12:00:00',
                            'updatedAt' => null,
                        ],
                        [
                            'project' => 1,
                            'projectName' => 'project1',
                            'createdBy' => 1,
                            'createdByFullName' => 'FirstName1 LastName1',
                            'id' => 2,
                            'name' => 'contract2',
                            'description' => 'contract-description2',
                            'proposedStartDate' => '2017-05-01',
                            'proposedEndDate' => '2017-08-01',
                            'forecastStartDate' => null,
                            'forecastEndDate' => null,
                            'createdAt' => '2017-01-01 12:00:00',
                            'updatedAt' => null,
                        ],
                    ],
                    'logo' => null,
                ],
            ],
        ];
    }

    /**
     * @dataProvider getDataForNumberIsUniqueOnEditAction
     *
     * @param array $content
     * @param $isResponseSuccessful
     * @param $responseStatusCode
     * @param $responseContent
     */
    public function testNumberIsUniqueOnEditAction(
        array $content,
        $isResponseSuccessful,
        $responseStatusCode,
        $responseContent
    ) {
        $user = $this->getUserByUsername('superadmin');
        $token = $user->getApiToken();

        $this->client->request(
            'PATCH',
            '/api/projects/1',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            json_encode($content)
        );
        $response = $this->client->getResponse();

        $this->assertEquals($isResponseSuccessful, $response->isClientError());
        $this->assertEquals($responseStatusCode, $response->getStatusCode());
        $this->assertEquals(json_encode($responseContent), $response->getContent());
    }

    /**
     * @return array
     */
    public function getDataForNumberIsUniqueOnEditAction()
    {
        return [
            [
                [
                    'number' => 'project-number-2',
                ],
                true,
                Response::HTTP_BAD_REQUEST,
                [
                    'messages' => [
                        'number' => ['That number is taken'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getDataForFieldsNotBlankOnEditAction
     *
     * @param array $content
     * @param $isResponseSuccessful
     * @param $responseStatusCode
     * @param $responseContent
     */
    public function testFieldsNotBlankOnEditAction(
        array $content,
        $isResponseSuccessful,
        $responseStatusCode,
        $responseContent
    ) {
        $user = $this->getUserByUsername('superadmin');
        $token = $user->getApiToken();

        $this->client->request(
            'PATCH',
            '/api/projects/1',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            json_encode($content)
        );
        $response = $this->client->getResponse();

        $this->assertEquals($isResponseSuccessful, $response->isClientError());
        $this->assertEquals($responseStatusCode, $response->getStatusCode());
        $this->assertEquals(json_encode($responseContent), $response->getContent());
    }

    /**
     * @return array
     */
    public function getDataForFieldsNotBlankOnEditAction()
    {
        return [
            [
                [
                    'name' => '',
                    'number' => '',
                ],
                true,
                Response::HTTP_BAD_REQUEST,
                [
                    'messages' => [
                        'name' => ['The name field should not be blank'],
                        'number' => ['The number field should not be blank'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getDataForDeleteAction()
     *
     * @param $isResponseSuccessful
     * @param $responseStatusCode
     */
    public function testDeleteAction(
        $isResponseSuccessful,
        $responseStatusCode
    ) {
        $project = (new Project())
            ->setName('project3')
            ->setNumber('project-number-3')
        ;
        $this->em->persist($project);
        $this->em->flush();

        $user = $this->getUserByUsername('superadmin');
        $token = $user->getApiToken();

        $this->client->request(
            'DELETE',
            sprintf('/api/projects/%d', $project->getId()),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            ''
        );
        $response = $this->client->getResponse();

        $this->assertEquals($isResponseSuccessful, $response->isSuccessful());
        $this->assertEquals($responseStatusCode, $response->getStatusCode());
    }

    /**d
     * @return array
     */
    public function getDataForDeleteAction()
    {
        return [
            [
                true,
                Response::HTTP_NO_CONTENT,
            ],
        ];
    }

    /**
     * @dataProvider getDataForGetAction()
     *
     * @param $url
     * @param $isResponseSuccessful
     * @param $responseStatusCode
     * @param $responseContent
     */
    public function testGetAction(
        $url,
        $isResponseSuccessful,
        $responseStatusCode,
        $responseContent
    ) {
        $user = $this->getUserByUsername('superadmin');
        $token = $user->getApiToken();

        $this->client->request(
            'GET',
            $url,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            ''
        );
        $response = $this->client->getResponse();
        $project = json_decode($response->getContent(), true);
        $responseContent['updatedAt'] = $project['updatedAt'];
        $responseContent['projectUsers'][0]['updatedAt'] = $project['projectUsers'][0]['updatedAt'];

        $this->assertEquals($isResponseSuccessful, $response->isSuccessful());
        $this->assertEquals($responseStatusCode, $response->getStatusCode());
        $this->assertEquals(json_encode($responseContent), $response->getContent());
    }

    /**
     * @return array
     */
    public function getDataForGetAction()
    {
        return [
            [
                '/api/projects/2',
                true,
                Response::HTTP_OK,
                [
                    'company' => null,
                    'companyName' => null,
                    'projectComplexity' => 2,
                    'projectComplexityName' => 'project-complexity2',
                    'projectCategory' => 2,
                    'projectCategoryName' => 'project-category2',
                    'projectScope' => 2,
                    'projectScopeName' => 'project-scope2',
                    'status' => 2,
                    'statusName' => 'project-status2',
                    'portfolio' => 2,
                    'portfolioName' => 'portfolio2',
                    'userFavorites' => [],
                    'id' => 2,
                    'name' => 'project2',
                    'number' => 'project-number-2',
                    'projectUsers' => [
                        [
                            'user' => 6,
                            'userFullName' => 'FirstName6 LastName6',
                            'project' => 2,
                            'projectName' => 'project2',
                            'projectCategory' => 2,
                            'projectCategoryName' => 'project-category2',
                            'projectRole' => 4,
                            'projectRoleName' => 'team-participant',
                            'projectDepartment' => 2,
                            'projectDepartmentName' => 'project-department2',
                            'projectTeam' => 2,
                            'projectTeamName' => 'project-team2',
                            'id' => 4,
                            'showInResources' => true,
                            'showInRaci' => null,
                            'showInOrg' => null,
                            'createdAt' => '2017-01-01 12:00:00',
                            'updatedAt' => null,
                        ],
                    ],
                    'projectTeams' => [],
                    'notes' => [],
                    'todos' => [],
                    'distributionLists' => [],
                    'statusUpdatedAt' => null,
                    'approvedAt' => null,
                    'createdAt' => '2017-01-01 12:00:00',
                    'updatedAt' => null,
                    'contracts' => [],
                    'logo' => null,
                ],
            ],
        ];
    }

    /**
     * @dataProvider getDataForCalendarsAction()
     *
     * @param $url
     * @param $isResponseSuccessful
     * @param $responseStatusCode
     * @param $responseContent
     */
    public function testCalendarsAction(
        $url,
        $isResponseSuccessful,
        $responseStatusCode,
        $responseContent
    ) {
        $user = $this->getUserByUsername('superadmin');
        $token = $user->getApiToken();

        $this->client->request('GET', $url, [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token)], '');
        $response = $this->client->getResponse();
        $this->assertEquals($isResponseSuccessful, $response->isSuccessful());
        $this->assertEquals($responseStatusCode, $response->getStatusCode());
        $this->assertEquals(json_encode($responseContent), $response->getContent());
    }

    /**
     * @return array
     */
    public function getDataForCalendarsAction()
    {
        return [
            [
                '/api/projects/1/calendars',
                true,
                Response::HTTP_OK,
                [
                    [
                        'project' => 1,
                        'projectName' => 'project1',
                        'parent' => null,
                        'parentName' => null,
                        'id' => 1,
                        'name' => 'calendar1',
                        'isBased' => true,
                        'isBaseline' => true,
                        'days' => [
                            [
                                'calendar' => 1,
                                'calendarName' => 'calendar1',
                                'id' => 1,
                                'type' => 1,
                                'working' => 5,
                                'workingTimes' => [
                                    [
                                        'day' => 1,
                                        'id' => 1,
                                        'fromTime' => '14:30:00',
                                        'toTime' => '18:30:00',
                                    ],
                                    [
                                        'day' => 1,
                                        'id' => 2,
                                        'fromTime' => '14:30:00',
                                        'toTime' => '18:30:00',
                                    ],
                                ],
                            ],
                            [
                                'calendar' => 1,
                                'calendarName' => 'calendar1',
                                'id' => 2,
                                'type' => 2,
                                'working' => 10,
                                'workingTimes' => [],
                            ],
                        ],
                        'workPackages' => [],
                        'workPackageProjectWorkCostTypes' => [],
                    ],
                    [
                        'project' => 1,
                        'projectName' => 'project1',
                        'parent' => null,
                        'parentName' => null,
                        'id' => 2,
                        'name' => 'calendar2',
                        'isBased' => true,
                        'isBaseline' => true,
                        'days' => [],
                        'workPackages' => [],
                        'workPackageProjectWorkCostTypes' => [],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getDataForCreateCalendarAction()
     *
     * @param array $content
     * @param $isResponseSuccessful
     * @param $responseStatusCode
     * @param $responseContent
     */
    public function testCreateCalendarAction(
        array $content,
        $isResponseSuccessful,
        $responseStatusCode,
        $responseContent
    ) {
        $user = $this->getUserByUsername('superadmin');
        $token = $user->getApiToken();

        $this->client->request('POST', '/api/projects/1/calendars', [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token)], json_encode($content));
        $response = $this->client->getResponse();

        $responseArray = json_decode($response->getContent(), true);
        $responseContent['id'] = $responseArray['id'];

        $this->assertEquals($isResponseSuccessful, $response->isSuccessful());
        $this->assertEquals($responseStatusCode, $response->getStatusCode());
        $this->assertEquals(json_encode($responseContent), $response->getContent());
    }

    /**
     * @return array
     */
    public function getDataForCreateCalendarAction()
    {
        return [
            [
                [
                    'name' => 'Calendar 2017',
                ],
                true,
                Response::HTTP_CREATED,
                [
                    'project' => 1,
                    'projectName' => 'project1',
                    'parent' => null,
                    'parentName' => null,
                    'id' => null,
                    'name' => 'Calendar 2017',
                    'isBased' => false,
                    'isBaseline' => false,
                    'days' => [],
                    'workPackages' => [],
                    'workPackageProjectWorkCostTypes' => [],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getDataForContractsAction()
     *
     * @param $url
     * @param $isResponseSuccessful
     * @param $responseStatusCode
     * @param $responseContent
     */
    public function testContractsAction(
        $url,
        $isResponseSuccessful,
        $responseStatusCode,
        $responseContent
    ) {
        $user = $this->getUserByUsername('superadmin');
        $token = $user->getApiToken();

        $this->client->request(
            'GET',
            $url,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token), ],
            ''
        );
        $response = $this->client->getResponse();

        $responseArray = json_decode($response->getContent(), true);
        $responseContent[0]['updatedAt'] = $responseArray[0]['updatedAt'];
        $responseContent[1]['updatedAt'] = $responseArray[1]['updatedAt'];

        $this->assertEquals($isResponseSuccessful, $response->isSuccessful());
        $this->assertEquals($responseStatusCode, $response->getStatusCode());
        $this->assertEquals(json_encode($responseContent), $response->getContent());
    }

    /**
     * @return array
     */
    public function getDataForContractsAction()
    {
        return [
            [
                '/api/projects/1/contracts',
                true,
                Response::HTTP_OK,
                [
                    [
                        'project' => 1,
                        'projectName' => 'project1',
                        'createdBy' => 1,
                        'createdByFullName' => 'FirstName1 LastName1',
                        'id' => 1,
                        'name' => 'contract1',
                        'description' => 'contract-description1',
                        'proposedStartDate' => '2017-01-01',
                        'proposedEndDate' => '2017-05-01',
                        'forecastStartDate' => null,
                        'forecastEndDate' => null,
                        'createdAt' => '2017-01-01 12:00:00',
                        'updatedAt' => null,
                    ],
                    [
                        'project' => 1,
                        'projectName' => 'project1',
                        'createdBy' => 1,
                        'createdByFullName' => 'FirstName1 LastName1',
                        'id' => 2,
                        'name' => 'contract2',
                        'description' => 'contract-description2',
                        'proposedStartDate' => '2017-05-01',
                        'proposedEndDate' => '2017-08-01',
                        'forecastStartDate' => null,
                        'forecastEndDate' => null,
                        'createdAt' => '2017-01-01 12:00:00',
                        'updatedAt' => null,
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getDataForCreateContractAction
     *
     * @param array $content
     * @param $isResponseSuccessful
     * @param $responseStatusCode
     * @param $responseContent
     */
    public function testCreateContractAction(
        array $content,
        $isResponseSuccessful,
        $responseStatusCode,
        $responseContent
    ) {
        $user = $this->getUserByUsername('superadmin');
        $token = $user->getApiToken();

        $this->client->request(
            'POST',
            '/api/projects/1/contracts',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            json_encode($content)
        );
        $response = $this->client->getResponse();

        $contract = json_decode($response->getContent(), true);
        $responseContent['createdAt'] = $contract['createdAt'];
        $responseContent['updatedAt'] = $contract['updatedAt'];

        $this->assertEquals($isResponseSuccessful, $response->isSuccessful());
        $this->assertEquals($responseStatusCode, $response->getStatusCode());
        $this->assertEquals(json_encode($responseContent), $response->getContent());

        $contract = $this
            ->em
            ->getRepository(Contract::class)
            ->find($contract['id'])
        ;
        $this->em->remove($contract);
        $this->em->flush();
    }

    /**
     * @return array
     */
    public function getDataForCreateContractAction()
    {
        return [
            [
                [
                    'name' => 'contract-test',
                ],
                true,
                Response::HTTP_CREATED,
                [
                    'project' => 1,
                    'projectName' => 'project1',
                    'createdBy' => 1,
                    'createdByFullName' => 'FirstName1 LastName1',
                    'id' => 4,
                    'name' => 'contract-test',
                    'description' => null,
                    'proposedStartDate' => null,
                    'proposedEndDate' => null,
                    'forecastStartDate' => null,
                    'forecastEndDate' => null,
                    'createdAt' => null,
                    'updatedAt' => null,
                ],
            ],
        ];
    }

    /**
     * @dataProvider getDataForDistributionListsAction()
     *
     * @param $url
     * @param $isResponseSuccessful
     * @param $responseStatusCode
     * @param $responseContent
     */
    public function testDistributionListsAction(
        $url,
        $isResponseSuccessful,
        $responseStatusCode,
        $responseContent
    ) {
        $user = $this->getUserByUsername('superadmin');
        $token = $user->getApiToken();

        $this->client->request(
            'GET',
            $url,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token), ],
            ''
        );

        $response = $this->client->getResponse();

        $responseArray = json_decode($response->getContent(), true);
        $responseArray[0]['updatedAt'] = null;
        $responseArray[1]['updatedAt'] = null;

        $responseContent[0]['users'][0]['apiToken'] = $responseArray[0]['users'][0]['apiToken'];
        $responseContent[1]['users'][0]['apiToken'] = $responseArray[1]['users'][0]['apiToken'];
        $responseContent[0]['users'][0]['updatedAt'] = $responseArray[0]['users'][0]['updatedAt'];
        $responseContent[1]['users'][0]['updatedAt'] = $responseArray[1]['users'][0]['updatedAt'];

        $responseArray = json_encode($responseArray);

        $this->assertEquals($isResponseSuccessful, $response->isSuccessful());
        $this->assertEquals($responseStatusCode, $response->getStatusCode());
        $this->assertEquals(json_encode($responseContent), $responseArray);
    }

    /**
     * @return array
     */
    public function getDataForDistributionListsAction()
    {
        return [
            [
                '/api/projects/1/distribution-lists',
                true,
                Response::HTTP_OK,
                [
                    [
                        'project' => 1,
                        'projectName' => 'project1',
                        'createdBy' => 1,
                        'createdByFullName' => 'FirstName1 LastName1',
                        'id' => 1,
                        'name' => 'distribution-list-1',
                        'sequence' => 1,
                        'users' => [
                            [
                                'roles' => ['ROLE_USER'],
                                'id' => 7,
                                'username' => 'user10',
                                'email' => 'user10@trisoft.ro',
                                'phone' => null,
                                'firstName' => 'FirstName10',
                                'lastName' => 'LastName10',
                                'isEnabled' => true,
                                'isSuspended' => false,
                                'createdAt' => '2017-01-01 00:00:00',
                                'updatedAt' => null,
                                'activatedAt' => null,
                                'teams' => [],
                                'apiToken' => null,
                                'widgetSettings' => [],
                                'facebook' => null,
                                'twitter' => null,
                                'instagram' => null,
                                'gplus' => null,
                                'linkedIn' => null,
                                'medium' => null,
                                'ownedDistributionLists' => [],
                                'contracts' => [],
                                'ownedMeetings' => [],
                                'avatar' => null,
                            ],
                        ],
                        'meetings' => [],
                        'createdAt' => '2017-01-01 07:00:00',
                        'updatedAt' => null,
                    ],
                    [
                        'project' => 1,
                        'projectName' => 'project1',
                        'createdBy' => 1,
                        'createdByFullName' => 'FirstName1 LastName1',
                        'id' => 2,
                        'name' => 'distribution-list-2',
                        'sequence' => 1,
                        'users' => [
                            [
                                'roles' => ['ROLE_USER'],
                                'id' => 7,
                                'username' => 'user10',
                                'email' => 'user10@trisoft.ro',
                                'phone' => null,
                                'firstName' => 'FirstName10',
                                'lastName' => 'LastName10',
                                'isEnabled' => true,
                                'isSuspended' => false,
                                'createdAt' => '2017-01-01 00:00:00',
                                'updatedAt' => null,
                                'activatedAt' => null,
                                'teams' => [],
                                'apiToken' => null,
                                'widgetSettings' => [],
                                'facebook' => null,
                                'twitter' => null,
                                'instagram' => null,
                                'gplus' => null,
                                'linkedIn' => null,
                                'medium' => null,
                                'ownedDistributionLists' => [],
                                'contracts' => [],
                                'ownedMeetings' => [],
                                'avatar' => null,
                            ],
                        ],
                        'meetings' => [],
                        'createdAt' => '2017-01-01 07:00:00',
                        'updatedAt' => null,
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getDataForCreateDistributionListAction()
     *
     * @param array $content
     * @param $isResponseSuccessful
     * @param $responseStatusCode
     * @param $responseContent
     */
    public function testCreateDistributionListAction(
        array $content,
        $isResponseSuccessful,
        $responseStatusCode,
        $responseContent
    ) {
        $user = $this->getUserByUsername('superadmin');
        $token = $user->getApiToken();

        $this->client->request(
            'POST',
            '/api/projects/1/distribution-lists',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            json_encode($content)
        );
        $response = $this->client->getResponse();

        $distributionList = json_decode($response->getContent(), true);
        $responseContent['createdAt'] = $distributionList['createdAt'];
        $responseContent['updatedAt'] = $distributionList['updatedAt'];
        $responseContent['id'] = $distributionList['id'];

        $this->assertEquals($isResponseSuccessful, $response->isSuccessful());
        $this->assertEquals($responseStatusCode, $response->getStatusCode());
        $this->assertEquals(json_encode($responseContent), $response->getContent());

        $distributionList = $this
            ->em
            ->getRepository(DistributionList::class)
            ->find($distributionList['id'])
        ;
        $this->em->remove($distributionList);
        $this->em->flush();
    }

    /**
     * @return array
     */
    public function getDataForCreateDistributionListAction()
    {
        return [
            [
                [
                    'name' => 'distribution-list-3',
                    'sequence' => 1,
                ],
                true,
                Response::HTTP_CREATED,
                [
                    'project' => 1,
                    'projectName' => 'project1',
                    'createdBy' => 1,
                    'createdByFullName' => 'FirstName1 LastName1',
                    'id' => null,
                    'name' => 'distribution-list-3',
                    'sequence' => 1,
                    'users' => [],
                    'meetings' => [],
                    'createdAt' => null,
                    'updatedAt' => null,
                ],
            ],
        ];
    }

    /**
     * @dataProvider getDataForLabelsAction()
     *
     * @param $url
     * @param $isResponseSuccessful
     * @param $responseStatusCode
     * @param $responseContent
     */
    public function testLabelsAction(
        $url,
        $isResponseSuccessful,
        $responseStatusCode,
        $responseContent
    ) {
        $user = $this->getUserByUsername('superadmin');
        $token = $user->getApiToken();

        $this->client->request(
            'GET',
            $url,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token), ],
            ''
        );
        $response = $this->client->getResponse();
        $this->assertEquals($isResponseSuccessful, $response->isSuccessful());
        $this->assertEquals($responseStatusCode, $response->getStatusCode());
        $this->assertEquals(json_encode($responseContent), $response->getContent());
    }

    /**
     * @return array
     */
    public function getDataForLabelsAction()
    {
        return [
            [
                '/api/projects/1/labels',
                true,
                Response::HTTP_OK,
                [
                    [
                        'project' => 1,
                        'projectName' => 'project1',
                        'id' => 1,
                        'title' => 'label-title1',
                        'description' => null,
                        'color' => 'color1',
                    ],
                    [
                        'project' => 1,
                        'projectName' => 'project1',
                        'id' => 2,
                        'title' => 'label-title2',
                        'description' => null,
                        'color' => 'color2',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getDataForCreateLabelAction()
     *
     * @param array $content
     * @param $isResponseSuccessful
     * @param $responseStatusCode
     * @param $responseContent
     */
    public function testCreateLabelAction(
        array $content,
        $isResponseSuccessful,
        $responseStatusCode,
        $responseContent
    ) {
        $user = $this->getUserByUsername('superadmin');
        $token = $user->getApiToken();

        $this->client->request(
            'POST',
            '/api/projects/2/labels',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            json_encode($content)
        );
        $response = $this->client->getResponse();

        $responseArray = json_decode($response->getContent(), true);
        $responseContent['id'] = $responseArray['id'];

        $this->assertEquals($isResponseSuccessful, $response->isSuccessful());
        $this->assertEquals($responseStatusCode, $response->getStatusCode());
        $this->assertEquals(json_encode($responseContent), $response->getContent());
    }

    /**
     * @return array
     */
    public function getDataForCreateLabelAction()
    {
        return [
            [
                [
                    'title' => 'label-title',
                    'color' => '123',
                ],
                true,
                Response::HTTP_CREATED,
                [
                    'project' => 2,
                    'projectName' => 'project2',
                    'id' => null,
                    'title' => 'label-title',
                    'description' => null,
                    'color' => '123',
                ],
            ],
        ];
    }

    /**
     * @dataProvider getDataForMeetingsAction()
     *
     * @param $isResponseSuccessful
     * @param $responseStatusCode
     * @param $responseContent
     */
    public function testMeetingsAction(
        $isResponseSuccessful,
        $responseStatusCode,
        $responseContent
    ) {
        $user = $this->getUserByUsername('superadmin');
        $token = $user->getApiToken();

        $this->client->request('GET', '/api/projects/1/meetings', [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token)], '');
        $response = $this->client->getResponse();

        $responseArray = json_decode($response->getContent(), true);
        $responseContent[0]['createdAt'] = $responseArray[0]['createdAt'];
        $responseContent[0]['updatedAt'] = $responseArray[0]['updatedAt'];

        $this->assertEquals($isResponseSuccessful, $response->isSuccessful());
        $this->assertEquals($responseStatusCode, $response->getStatusCode());
        $this->assertEquals(json_encode($responseContent), $response->getContent());
    }

    /**
     * @return array
     */
    public function getDataForMeetingsAction()
    {
        return [
            [
                true,
                Response::HTTP_OK,
                [
                    [
                        'project' => 1,
                        'projectName' => 'project1',
                        'createdBy' => null,
                        'createdByFullName' => null,
                        'id' => 1,
                        'name' => 'meeting1',
                        'location' => 'location1',
                        'date' => '2017-01-01 00:00:00',
                        'start' => '07:00:00',
                        'end' => '12:00:00',
                        'objectives' => 'objectives',
                        'meetingParticipants' => [
                            [
                                'meeting' => 1,
                                'meetingName' => 'meeting1',
                                'user' => 4,
                                'userFullName' => 'FirstName4 LastName4',
                                'id' => 1,
                                'remark' => null,
                                'isPresent' => false,
                                'isExcused' => false,
                                'inDistributionList' => false,
                            ],
                            [
                                'meeting' => 1,
                                'meetingName' => 'meeting1',
                                'user' => 5,
                                'userFullName' => 'FirstName5 LastName5',
                                'id' => 2,
                                'remark' => null,
                                'isPresent' => false,
                                'isExcused' => false,
                                'inDistributionList' => false,
                            ],
                        ],
                        'meetingAgendas' => [
                            [
                                'meeting' => 1,
                                'meetingName' => 'meeting1',
                                'responsibility' => 3,
                                'responsibilityFullName' => 'FirstName3 LastName3',
                                'id' => 1,
                                'topic' => 'topic1',
                                'start' => '07:30:00',
                                'end' => '08:00:00',
                                'duration' => '00:30:00',
                            ],
                            [
                                'meeting' => 1,
                                'meetingName' => 'meeting1',
                                'responsibility' => 3,
                                'responsibilityFullName' => 'FirstName3 LastName3',
                                'id' => 2,
                                'topic' => 'topic2',
                                'start' => '11:30:00',
                                'end' => '12:00:00',
                                'duration' => '00:30:00',
                            ],
                        ],
                        'medias' => [
                            [
                                'fileSystem' => 1,
                                'fileSystemName' => 'fs',
                                'user' => 1,
                                'userFullName' => 'FirstName1 LastName1',
                                'id' => 1,
                                'path' => 'file1',
                                'mimeType' => 'inode/x-empty',
                                'fileSize' => 0,
                                'createdAt' => '2017-01-01 00:00:00',
                            ],
                        ],
                        'decisions' => [
                            [
                                'status' => 1,
                                'statusName' => 'status1',
                                'meeting' => 1,
                                'meetingName' => 'meeting1',
                                'project' => 1,
                                'projectName' => 'project1',
                                'responsibility' => 4,
                                'responsibilityFullName' => 'FirstName4 LastName4',
                                'id' => 1,
                                'title' => 'decision1',
                                'description' => 'description1',
                                'showInStatusReport' => false,
                                'date' => '2017-01-01 00:00:00',
                                'dueDate' => '2017-05-01 00:00:00',
                            ],
                            [
                                'status' => 1,
                                'statusName' => 'status1',
                                'meeting' => 1,
                                'meetingName' => 'meeting1',
                                'project' => 1,
                                'projectName' => 'project1',
                                'responsibility' => 4,
                                'responsibilityFullName' => 'FirstName4 LastName4',
                                'id' => 2,
                                'title' => 'decision2',
                                'description' => 'description2',
                                'showInStatusReport' => false,
                                'date' => '2017-01-01 00:00:00',
                                'dueDate' => '2017-05-01 00:00:00',
                            ],
                        ],
                        'todos' => [
                            [
                                'status' => 1,
                                'statusName' => 'status1',
                                'meeting' => 1,
                                'meetingName' => 'meeting1',
                                'project' => 1,
                                'projectName' => 'project1',
                                'responsibility' => 4,
                                'responsibilityFullName' => 'FirstName4 LastName4',
                                'id' => 1,
                                'title' => 'todo1',
                                'description' => 'description for todo1',
                                'showInStatusReport' => false,
                                'date' => '2017-01-01 00:00:00',
                                'dueDate' => '2017-05-01 00:00:00',
                            ],
                            [
                                'status' => 1,
                                'statusName' => 'status1',
                                'meeting' => 1,
                                'meetingName' => 'meeting1',
                                'project' => 1,
                                'projectName' => 'project1',
                                'responsibility' => 4,
                                'responsibilityFullName' => 'FirstName4 LastName4',
                                'id' => 2,
                                'title' => 'todo2',
                                'description' => 'description for todo2',
                                'showInStatusReport' => false,
                                'date' => '2017-01-01 00:00:00',
                                'dueDate' => '2017-05-01 00:00:00',
                            ],
                        ],
                        'notes' => [
                            [
                                'status' => 1,
                                'statusName' => 'status1',
                                'meeting' => 1,
                                'meetingName' => 'meeting1',
                                'project' => 1,
                                'projectName' => 'project1',
                                'responsibility' => 4,
                                'responsibilityFullName' => 'FirstName4 LastName4',
                                'id' => 1,
                                'title' => 'note1',
                                'description' => 'description1',
                                'showInStatusReport' => false,
                                'date' => '2017-01-01 00:00:00',
                                'dueDate' => '2017-05-01 00:00:00',
                            ],
                            [
                                'status' => 1,
                                'statusName' => 'status1',
                                'meeting' => 1,
                                'meetingName' => 'meeting1',
                                'project' => 1,
                                'projectName' => 'project1',
                                'responsibility' => 4,
                                'responsibilityFullName' => 'FirstName4 LastName4',
                                'id' => 2,
                                'title' => 'note2',
                                'description' => 'description2',
                                'showInStatusReport' => false,
                                'date' => '2017-01-01 00:00:00',
                                'dueDate' => '2017-05-01 00:00:00',
                            ],
                        ],
                        'distributionLists' => [],
                        'createdAt' => null,
                        'updatedAt' => null,
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getDataForCreateMeetingAction
     *
     * @param array $content
     * @param $isResponseSuccessful
     * @param $responseStatusCode
     * @param $responseContent
     */
    public function testCreateMeetingAction(
        array $content,
        $isResponseSuccessful,
        $responseStatusCode,
        $responseContent
    ) {
        $user = $this->getUserByUsername('superadmin');
        $token = $user->getApiToken();

        $this->client->request('POST', '/api/projects/1/meetings', [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token)], json_encode($content));
        $response = $this->client->getResponse();

        $responseArray = json_decode($response->getContent(), true);
        $responseContent['id'] = $responseArray['id'];
        $responseContent['createdAt'] = $responseArray['createdAt'];
        $responseContent['updatedAt'] = $responseArray['updatedAt'];

        $this->assertEquals($isResponseSuccessful, $response->isSuccessful());
        $this->assertEquals($responseStatusCode, $response->getStatusCode());
        $this->assertEquals(json_encode($responseContent), $response->getContent());
    }

    /**
     * @return array
     */
    public function getDataForCreateMeetingAction()
    {
        return [
            [
                [
                    'name' => 'meet',
                    'location' => 'loc1',
                    'objectives' => 'objectives',
                    'date' => '07-01-2017',
                    'start' => '16:00:00',
                    'end' => '17:00:00',
                ],
                true,
                Response::HTTP_CREATED,
                [
                    'project' => 1,
                    'projectName' => 'project1',
                    'createdBy' => null,
                    'createdByFullName' => null,
                    'id' => null,
                    'name' => 'meet',
                    'location' => 'loc1',
                    'date' => '2017-01-07 00:00:00',
                    'start' => '16:00:00',
                    'end' => '17:00:00',
                    'objectives' => 'objectives',
                    'meetingParticipants' => [],
                    'meetingAgendas' => [],
                    'medias' => [],
                    'decisions' => [],
                    'todos' => [],
                    'notes' => [],
                    'distributionLists' => [],
                    'createdAt' => null,
                    'updatedAt' => null,
                ],
            ],
        ];
    }

    /**
     * @dataProvider getDataForNotesAction()
     *
     * @param $url
     * @param $isResponseSuccessful
     * @param $responseStatusCode
     * @param $responseContent
     */
    public function testNotesAction(
        $url,
        $isResponseSuccessful,
        $responseStatusCode,
        $responseContent
    ) {
        $user = $this->getUserByUsername('superadmin');
        $token = $user->getApiToken();

        $this->client->request('GET', $url, [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token)], '');
        $response = $this->client->getResponse();
        $this->assertEquals($isResponseSuccessful, $response->isSuccessful());
        $this->assertEquals($responseStatusCode, $response->getStatusCode());
        $this->assertEquals(json_encode($responseContent), $response->getContent());
    }

    /**
     * @return array
     */
    public function getDataForNotesAction()
    {
        return [
            [
                '/api/projects/1/notes',
                true,
                Response::HTTP_OK,
                [
                    [

                        'status' => 1,
                        'statusName' => 'status1',
                        'meeting' => 1,
                        'meetingName' => 'meeting1',
                        'project' => 1,
                        'projectName' => 'project1',
                        'responsibility' => 4,
                        'responsibilityFullName' => 'FirstName4 LastName4',
                        'id' => 1,
                        'title' => 'note1',
                        'description' => 'description1',
                        'showInStatusReport' => false,
                        'date' => '2017-01-01 00:00:00',
                        'dueDate' => '2017-05-01 00:00:00',
                    ],
                    [
                        'status' => 1,
                        'statusName' => 'status1',
                        'meeting' => 1,
                        'meetingName' => 'meeting1',
                        'project' => 1,
                        'projectName' => 'project1',
                        'responsibility' => 4,
                        'responsibilityFullName' => 'FirstName4 LastName4',
                        'id' => 2,
                        'title' => 'note2',
                        'description' => 'description2',
                        'showInStatusReport' => false,
                        'date' => '2017-01-01 00:00:00',
                        'dueDate' => '2017-05-01 00:00:00',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getDataForCreateNoteAction()
     *
     * @param array $content
     * @param $isResponseSuccessful
     * @param $responseStatusCode
     * @param $responseContent
     */
    public function testCreateNoteAction(
        array $content,
        $isResponseSuccessful,
        $responseStatusCode,
        $responseContent
    ) {
        $user = $this->getUserByUsername('superadmin');
        $token = $user->getApiToken();

        $this->client->request('POST', '/api/projects/1/notes', [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token)], json_encode($content));
        $response = $this->client->getResponse();

        $responseArray = json_decode($response->getContent(), true);
        $responseContent['id'] = $responseArray['id'];

        $this->assertEquals($isResponseSuccessful, $response->isSuccessful());
        $this->assertEquals($responseStatusCode, $response->getStatusCode());
        $this->assertEquals(json_encode($responseContent), $response->getContent());
    }

    /**
     * @return array
     */
    public function getDataForCreateNoteAction()
    {
        return [
            [
                [
                    'title' => 'note project 1',
                    'description' => 'descript',
                ],
                true,
                Response::HTTP_CREATED,
                [
                    'status' => null,
                    'statusName' => null,
                    'meeting' => null,
                    'meetingName' => null,
                    'project' => 1,
                    'projectName' => 'project1',
                    'responsibility' => null,
                    'responsibilityFullName' => null,
                    'id' => null,
                    'title' => 'note project 1',
                    'description' => 'descript',
                    'showInStatusReport' => false,
                    'date' => null,
                    'dueDate' => null,
                ],
            ],
        ];
    }

    /**
     * @dataProvider getDataForProjectTeamsAction()
     *
     * @param $url
     * @param $isResponseSuccessful
     * @param $responseStatusCode
     * @param $responseContent
     */
    public function testProjectTeamsAction(
        $url,
        $isResponseSuccessful,
        $responseStatusCode,
        $responseContent
    ) {
        $user = $this->getUserByUsername('superadmin');
        $token = $user->getApiToken();

        $this->client->request(
            'GET',
            $url,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            ''
        );
        $response = $this->client->getResponse();
        for ($i = 1; $i <= 2; ++$i) {
            $pm = $this->em->getRepository(ProjectTeam::class)->find($i);
            $responseContent[$i - 1]['updatedAt'] = $pm->getUpdatedAt()->format('Y-m-d H:i:s');
        }

        $this->assertEquals($isResponseSuccessful, $response->isSuccessful());
        $this->assertEquals($responseStatusCode, $response->getStatusCode());
        $this->assertEquals(json_encode($responseContent), $response->getContent());
    }

    /**
     * @return array
     */
    public function getDataForProjectTeamsAction()
    {
        return [
            [
                '/api/projects/1/project-teams',
                true,
                Response::HTTP_OK,
                [
                    [
                        'project' => 1,
                        'projectName' => 'project1',
                        'parent' => null,
                        'parentName' => null,
                        'id' => 1,
                        'name' => 'project-team1',
                        'createdAt' => '2017-01-01 12:00:00',
                        'updatedAt' => null,
                        'children' => [],
                    ],
                    [
                        'project' => 1,
                        'projectName' => 'project1',
                        'parent' => null,
                        'parentName' => null,
                        'id' => 2,
                        'name' => 'project-team2',
                        'createdAt' => '2017-01-01 12:00:00',
                        'updatedAt' => null,
                        'children' => [],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getDataForCreateProjectTeamAction()
     *
     * @param array $content
     * @param $isResponseSuccessful
     * @param $responseStatusCode
     * @param $responseContent
     */
    public function testCreateProjectTeamAction(
        array $content,
        $isResponseSuccessful,
        $responseStatusCode,
        $responseContent
    ) {
        $user = $this->getUserByUsername('superadmin');
        $token = $user->getApiToken();

        $this->client->request(
            'POST',
            '/api/projects/1/project-teams',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            json_encode($content)
        );
        $response = $this->client->getResponse();

        $projectTeam = json_decode($response->getContent(), true);
        $responseContent['createdAt'] = $projectTeam['createdAt'];
        $responseContent['updatedAt'] = $projectTeam['updatedAt'];

        $this->assertEquals($isResponseSuccessful, $response->isSuccessful());
        $this->assertEquals($responseStatusCode, $response->getStatusCode());
        $this->assertEquals(json_encode($responseContent), $response->getContent());

        $projectTeam = $this
            ->em
            ->getRepository(ProjectTeam::class)
            ->find($projectTeam['id'])
        ;
        $this->em->remove($projectTeam);
        $this->em->flush();
    }

    /**
     * @return array
     */
    public function getDataForCreateProjectTeamAction()
    {
        return [
            [
                [
                    'name' => 'project-team3',
                ],
                true,
                Response::HTTP_CREATED,
                [
                    'project' => 1,
                    'projectName' => 'project1',
                    'parent' => null,
                    'parentName' => null,
                    'id' => 3,
                    'name' => 'project-team3',
                    'createdAt' => '',
                    'updatedAt' => null,
                    'children' => [],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getDataForProjectUsersAction()
     *
     * @param $url
     * @param $isResponseSuccessful
     * @param $responseStatusCode
     * @param $responseContent
     */
    public function testProjectUsersAction(
        $url,
        $isResponseSuccessful,
        $responseStatusCode,
        $responseContent
    ) {
        $user = $this->getUserByUsername('superadmin');
        $token = $user->getApiToken();

        $this->client->request(
            'GET',
            $url,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token), ],
            ''
        );
        $response = $this->client->getResponse();

        $responseArray = json_decode($response->getContent(), true);
        for ($i = 0; $i < 3; ++$i) {
            $responseContent[$i]['updatedAt'] = $responseArray[$i]['updatedAt'];
        }

        $this->assertEquals($isResponseSuccessful, $response->isSuccessful());
        $this->assertEquals($responseStatusCode, $response->getStatusCode());
        $this->assertEquals(json_encode($responseContent), $response->getContent());
    }

    /**
     * @return array
     */
    public function getDataForProjectUsersAction()
    {
        return [
            [
                '/api/projects/1/project-users',
                true,
                Response::HTTP_OK,
                [
                    [
                        'user' => 3,
                        'userFullName' => 'FirstName3 LastName3',
                        'project' => 1,
                        'projectName' => 'project1',
                        'projectCategory' => 1,
                        'projectCategoryName' => 'project-category1',
                        'projectRole' => 1,
                        'projectRoleName' => 'manager',
                        'projectDepartment' => 1,
                        'projectDepartmentName' => 'project-department1',
                        'projectTeam' => 1,
                        'projectTeamName' => 'project-team1',
                        'id' => 1,
                        'showInResources' => true,
                        'showInRaci' => null,
                        'showInOrg' => null,
                        'createdAt' => '2017-01-01 12:00:00',
                        'updatedAt' => null,
                    ],
                    [
                        'user' => 4,
                        'userFullName' => 'FirstName4 LastName4',
                        'project' => 1,
                        'projectName' => 'project1',
                        'projectCategory' => 2,
                        'projectCategoryName' => 'project-category2',
                        'projectRole' => 2,
                        'projectRoleName' => 'sponsor',
                        'projectDepartment' => 2,
                        'projectDepartmentName' => 'project-department2',
                        'projectTeam' => 2,
                        'projectTeamName' => 'project-team2',
                        'id' => 2,
                        'showInResources' => true,
                        'showInRaci' => null,
                        'showInOrg' => null,
                        'createdAt' => '2017-01-01 12:00:00',
                        'updatedAt' => null,
                    ],
                    [
                        'user' => 5,
                        'userFullName' => 'FirstName5 LastName5',
                        'project' => 1,
                        'projectName' => 'project1',
                        'projectCategory' => 1,
                        'projectCategoryName' => 'project-category1',
                        'projectRole' => 3,
                        'projectRoleName' => 'team-member',
                        'projectDepartment' => 1,
                        'projectDepartmentName' => 'project-department1',
                        'projectTeam' => 1,
                        'projectTeamName' => 'project-team1',
                        'id' => 3,
                        'showInResources' => true,
                        'showInRaci' => null,
                        'showInOrg' => null,
                        'createdAt' => '2017-01-01 12:00:00',
                        'updatedAt' => null,
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getDataForCreateProjectUserAction()
     *
     * @param array $content
     * @param $isResponseSuccessful
     * @param $responseStatusCode
     * @param $responseContent
     */
    public function testCreateProjectUserAction(
        array $content,
        $isResponseSuccessful,
        $responseStatusCode,
        $responseContent
    ) {
        $user = $this->getUserByUsername('superadmin');
        $token = $user->getApiToken();

        $this->client->request(
            'POST',
            '/api/projects/1/project-users',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            json_encode($content)
        );
        $response = $this->client->getResponse();

        $projectUser = json_decode($response->getContent(), true);
        $responseContent['createdAt'] = $projectUser['createdAt'];
        $responseContent['updatedAt'] = $projectUser['updatedAt'];
        $responseContent['id'] = $projectUser['id'];

        $this->assertEquals($isResponseSuccessful, $response->isSuccessful());
        $this->assertEquals($responseStatusCode, $response->getStatusCode());
        $this->assertEquals(json_encode($responseContent), $response->getContent());

        $projectUser = $this
            ->em
            ->getRepository(ProjectUser::class)
            ->find($projectUser['id'])
        ;
        $this->em->remove($projectUser);
        $this->em->flush();
    }

    /**
     * @return array
     */
    public function getDataForCreateProjectUserAction()
    {
        return [
            [
                [
                    'user' => 6,
                ],
                true,
                Response::HTTP_CREATED,
                [
                    'user' => 6,
                    'userFullName' => 'FirstName6 LastName6',
                    'project' => 1,
                    'projectName' => 'project1',
                    'projectCategory' => null,
                    'projectCategoryName' => null,
                    'projectRole' => null,
                    'projectRoleName' => null,
                    'projectDepartment' => null,
                    'projectDepartmentName' => null,
                    'projectTeam' => null,
                    'projectTeamName' => null,
                    'id' => null,
                    'showInResources' => false,
                    'showInRaci' => false,
                    'showInOrg' => false,
                    'createdAt' => '',
                    'updatedAt' => null,
                ],
            ],
        ];
    }

    /**
     * @dataProvider getDataForFieldsNotBlankOnCreateProjectUserAction()
     *
     * @param array $content
     * @param $isResponseSuccessful
     * @param $responseStatusCode
     * @param $responseContent
     */
    public function testFieldsNotBlankOnCreateProjectUserAction(
        array $content,
        $isResponseSuccessful,
        $responseStatusCode,
        $responseContent
    ) {
        $user = $this->getUserByUsername('superadmin');
        $token = $user->getApiToken();

        $this->client->request(
            'POST',
            '/api/projects/1/project-users',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            json_encode($content)
        );
        $response = $this->client->getResponse();
        $this->assertEquals($isResponseSuccessful, $response->isSuccessful());
        $this->assertEquals($responseStatusCode, $response->getStatusCode());
        $this->assertEquals(json_encode($responseContent), $response->getContent());
    }

    /**
     * @return array
     */
    public function getDataForFieldsNotBlankOnCreateProjectUserAction()
    {
        return [
            [
                [],
                false,
                Response::HTTP_BAD_REQUEST,
                [
                    'messages' => [
                        'user' => ['The name field should not be blank. Choose one user'],
                    ],

                ],
            ],
        ];
    }

    /**
     * @dataProvider getDataForTodosAction()
     *
     * @param $url
     * @param $isResponseSuccessful
     * @param $responseStatusCode
     * @param $responseContent
     */
    public function testTodosAction(
        $url,
        $isResponseSuccessful,
        $responseStatusCode,
        $responseContent
    ) {
        $user = $this->getUserByUsername('superadmin');
        $token = $user->getApiToken();

        $this->client->request('GET', $url, [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token)], '');
        $response = $this->client->getResponse();
        $this->assertEquals($isResponseSuccessful, $response->isSuccessful());
        $this->assertEquals($responseStatusCode, $response->getStatusCode());
        $this->assertEquals(json_encode($responseContent), $response->getContent());
    }

    /**
     * @return array
     */
    public function getDataForTodosAction()
    {
        return [
            [
                '/api/projects/1/todos',
                true,
                Response::HTTP_OK,
                [
                    [

                        'status' => 1,
                        'statusName' => 'status1',
                        'meeting' => 1,
                        'meetingName' => 'meeting1',
                        'project' => 1,
                        'projectName' => 'project1',
                        'responsibility' => 4,
                        'responsibilityFullName' => 'FirstName4 LastName4',
                        'id' => 1,
                        'title' => 'todo1',
                        'description' => 'description for todo1',
                        'showInStatusReport' => false,
                        'date' => '2017-01-01 00:00:00',
                        'dueDate' => '2017-05-01 00:00:00',
                    ],
                    [
                        'status' => 1,
                        'statusName' => 'status1',
                        'meeting' => 1,
                        'meetingName' => 'meeting1',
                        'project' => 1,
                        'projectName' => 'project1',
                        'responsibility' => 4,
                        'responsibilityFullName' => 'FirstName4 LastName4',
                        'id' => 2,
                        'title' => 'todo2',
                        'description' => 'description for todo2',
                        'showInStatusReport' => false,
                        'date' => '2017-01-01 00:00:00',
                        'dueDate' => '2017-05-01 00:00:00',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getDataForCreateTodoAction()
     *
     * @param array $content
     * @param $isResponseSuccessful
     * @param $responseStatusCode
     * @param $responseContent
     */
    public function testCreateTodoAction(
        array $content,
        $isResponseSuccessful,
        $responseStatusCode,
        $responseContent
    ) {
        $user = $this->getUserByUsername('superadmin');
        $token = $user->getApiToken();

        $this->client->request('POST', '/api/projects/1/todos', [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token)], json_encode($content));
        $response = $this->client->getResponse();

        $responseArray = json_decode($response->getContent(), true);
        $responseContent['id'] = $responseArray['id'];

        $this->assertEquals($isResponseSuccessful, $response->isSuccessful());
        $this->assertEquals($responseStatusCode, $response->getStatusCode());
        $this->assertEquals(json_encode($responseContent), $response->getContent());
    }

    /**
     * @return array
     */
    public function getDataForCreateTodoAction()
    {
        return [
            [
                [
                    'title' => 'do this',
                    'description' => 'descript',
                ],
                true,
                Response::HTTP_CREATED,
                [
                    'status' => null,
                    'statusName' => null,
                    'meeting' => null,
                    'meetingName' => null,
                    'project' => 1,
                    'projectName' => 'project1',
                    'responsibility' => null,
                    'responsibilityFullName' => null,
                    'id' => null,
                    'title' => 'do this',
                    'description' => 'descript',
                    'showInStatusReport' => false,
                    'date' => null,
                    'dueDate' => null,
                ],
            ],
        ];
    }

    /**
     * @dataProvider getDataForWppwctsAction()
     *
     * @param $url
     * @param $isResponseSuccessful
     * @param $responseStatusCode
     * @param $responseContent
     */
    public function testWppwctsAction(
        $url,
        $isResponseSuccessful,
        $responseStatusCode,
        $responseContent
    ) {
        $user = $this->getUserByUsername('superadmin');
        $token = $user->getApiToken();

        $this->client->request(
            'GET',
            $url,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token), ],
            ''
        );
        $response = $this->client->getResponse();
        $this->assertEquals($isResponseSuccessful, $response->isSuccessful());
        $this->assertEquals($responseStatusCode, $response->getStatusCode());
        $this->assertEquals(json_encode($responseContent), $response->getContent());
    }

    /**
     * @return array
     */
    public function getDataForWppwctsAction()
    {
        return [
            [
                '/api/projects/1/wppwcts',
                true,
                Response::HTTP_OK,
                [
                    [
                        'workPackage' => 1,
                        'workPackageName' => 'work-package1',
                        'projectWorkCostType' => 1,
                        'projectWorkCostTypeName' => 'project-work-cost-type1',
                        'calendar' => null,
                        'calendarName' => null,
                        'id' => 1,
                        'name' => 'work-package-project-work-cost-type1',
                        'base' => null,
                        'change' => null,
                        'actual' => null,
                        'remaining' => null,
                        'forecast' => null,
                        'isGeneric' => false,
                        'isInactive' => false,
                        'isEnterprise' => false,
                        'isCostResource' => false,
                        'isBudget' => false,
                        'createdAt' => '2017-01-20',
                    ],
                    [
                        'workPackage' => 2,
                        'workPackageName' => 'work-package2',
                        'projectWorkCostType' => 2,
                        'projectWorkCostTypeName' => 'project-work-cost-type2',
                        'calendar' => null,
                        'calendarName' => null,
                        'id' => 2,
                        'name' => 'work-package-project-work-cost-type2',
                        'base' => null,
                        'change' => null,
                        'actual' => null,
                        'remaining' => null,
                        'forecast' => null,
                        'isGeneric' => false,
                        'isInactive' => false,
                        'isEnterprise' => false,
                        'isCostResource' => false,
                        'isBudget' => false,
                        'createdAt' => '2017-01-20',
                    ],
                ],
            ],
        ];
    }
}
