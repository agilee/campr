<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Workspace.
 *
 * @ORM\Table(name="work_package")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\WorkPackageRepository")
 */
class WorkPackage
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Project Unique ID - An ID that is unique within the project workspace.
     *
     * @var string
     *
     * @ORM\Column(name="puid", type="string", length=128)
     */
    private $puid;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var WorkPackage
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\WorkPackage")
     * @ORM\JoinColumn(name="parent_id")
     */
    private $parent;

    /**
     * @var ColorStatus|null
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ColorStatus")
     * @ORM\JoinColumn(name="color_status_id")
     */
    private $colorStatus;

    /**
     * @var int
     * @ORM\Column(name="progress", type="integer", options={"default": 0})
     */
    private $progress = 0;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="responsibility_id")
     */
    private $responsibility;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="scheduled_start_at", type="date", nullable=true)
     */
    private $scheduledStartAt;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="scheduled_finish_at", type="date", nullable=true)
     */
    private $scheduledFinishAt;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="forecast_start_at", type="date", nullable=true)
     */
    private $forecastStartAt;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="forecast_finish_at", type="date", nullable=true)
     */
    private $forecastFinishAt;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="actual_start_at", type="date", nullable=true)
     */
    private $actualStartAt;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="actual_finish_at", type="date", nullable=true)
     */
    private $actualFinishAt;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="results", type="text", nullable=true)
     */
    private $results;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_key_milestone", type="boolean", nullable=false, options={"default"=0})
     */
    private $isKeyMilestone = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set puid.
     *
     * @param string $puid
     *
     * @return WorkPackage
     */
    public function setPuid($puid)
    {
        $this->puid = $puid;

        return $this;
    }

    /**
     * Get puid.
     *
     * @return string
     */
    public function getPuid()
    {
        return $this->puid;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return WorkPackage
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set progress.
     *
     * @param int $progress
     *
     * @return WorkPackage
     */
    public function setProgress($progress)
    {
        $this->progress = $progress;

        return $this;
    }

    /**
     * Get progress.
     *
     * @return int
     */
    public function getProgress()
    {
        return $this->progress;
    }

    /**
     * Set scheduledStartAt.
     *
     * @param \DateTime $scheduledStartAt
     *
     * @return WorkPackage
     */
    public function setScheduledStartAt($scheduledStartAt)
    {
        $this->scheduledStartAt = $scheduledStartAt;

        return $this;
    }

    /**
     * Get scheduledStartAt.
     *
     * @return \DateTime
     */
    public function getScheduledStartAt()
    {
        return $this->scheduledStartAt;
    }

    /**
     * Set scheduledFinishAt.
     *
     * @param \DateTime $scheduledFinishAt
     *
     * @return WorkPackage
     */
    public function setScheduledFinishAt($scheduledFinishAt)
    {
        $this->scheduledFinishAt = $scheduledFinishAt;

        return $this;
    }

    /**
     * Get scheduledFinishAt.
     *
     * @return \DateTime
     */
    public function getScheduledFinishAt()
    {
        return $this->scheduledFinishAt;
    }

    /**
     * Set forecastStartAt.
     *
     * @param \DateTime $forecastStartAt
     *
     * @return WorkPackage
     */
    public function setForecastStartAt($forecastStartAt)
    {
        $this->forecastStartAt = $forecastStartAt;

        return $this;
    }

    /**
     * Get forecastStartAt.
     *
     * @return \DateTime
     */
    public function getForecastStartAt()
    {
        return $this->forecastStartAt;
    }

    /**
     * Set forecastFinishAt.
     *
     * @param \DateTime $forecastFinishAt
     *
     * @return WorkPackage
     */
    public function setForecastFinishAt($forecastFinishAt)
    {
        $this->forecastFinishAt = $forecastFinishAt;

        return $this;
    }

    /**
     * Get forecastFinishAt.
     *
     * @return \DateTime
     */
    public function getForecastFinishAt()
    {
        return $this->forecastFinishAt;
    }

    /**
     * Set actualStartAt.
     *
     * @param \DateTime $actualStartAt
     *
     * @return WorkPackage
     */
    public function setActualStartAt($actualStartAt)
    {
        $this->actualStartAt = $actualStartAt;

        return $this;
    }

    /**
     * Get actualStartAt.
     *
     * @return \DateTime
     */
    public function getActualStartAt()
    {
        return $this->actualStartAt;
    }

    /**
     * Set actualFinishAt.
     *
     * @param \DateTime $actualFinishAt
     *
     * @return WorkPackage
     */
    public function setActualFinishAt($actualFinishAt)
    {
        $this->actualFinishAt = $actualFinishAt;

        return $this;
    }

    /**
     * Get actualFinishAt.
     *
     * @return \DateTime
     */
    public function getActualFinishAt()
    {
        return $this->actualFinishAt;
    }

    /**
     * Set content.
     *
     * @param string $content
     *
     * @return WorkPackage
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set results.
     *
     * @param string $results
     *
     * @return WorkPackage
     */
    public function setResults($results)
    {
        $this->results = $results;

        return $this;
    }

    /**
     * Get results.
     *
     * @return string
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Set isKeyMilestone.
     *
     * @param bool $isKeyMilestone
     *
     * @return WorkPackage
     */
    public function setIsKeyMilestone($isKeyMilestone)
    {
        $this->isKeyMilestone = $isKeyMilestone;

        return $this;
    }

    /**
     * Get isKeyMilestone.
     *
     * @return bool
     */
    public function getIsKeyMilestone()
    {
        return $this->isKeyMilestone;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return WorkPackage
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt.
     *
     * @param \DateTime $updatedAt
     *
     * @return WorkPackage
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set parent.
     *
     * @param \AppBundle\Entity\WorkPackage $parent
     *
     * @return WorkPackage
     */
    public function setParent(\AppBundle\Entity\WorkPackage $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent.
     *
     * @return \AppBundle\Entity\WorkPackage
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set colorStatus.
     *
     * @param \AppBundle\Entity\ColorStatus $colorStatus
     *
     * @return WorkPackage
     */
    public function setColorStatus(\AppBundle\Entity\ColorStatus $colorStatus = null)
    {
        $this->colorStatus = $colorStatus;

        return $this;
    }

    /**
     * Get colorStatus.
     *
     * @return \AppBundle\Entity\ColorStatus
     */
    public function getColorStatus()
    {
        return $this->colorStatus;
    }

    /**
     * Set responsibility.
     *
     * @param \AppBundle\Entity\User $responsibility
     *
     * @return WorkPackage
     */
    public function setResponsibility(\AppBundle\Entity\User $responsibility = null)
    {
        $this->responsibility = $responsibility;

        return $this;
    }

    /**
     * Get responsibility.
     *
     * @return \AppBundle\Entity\User
     */
    public function getResponsibility()
    {
        return $this->responsibility;
    }
}
