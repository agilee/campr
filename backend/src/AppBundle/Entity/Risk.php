<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Risk.
 *
 * @ORM\Table(name="risk")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RiskRepository")
 */
class Risk
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
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var Impact
     *
     * @Serializer\Exclude()
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Impact")
     * @ORM\JoinColumn(name="impact_id")
     */
    private $impact;

    /**
     * @var string
     *
     * @ORM\Column(name="cost", type="string", length=255)
     */
    private $cost;

    /**
     * @var string
     *
     * @ORM\Column(name="budget", type="string", length=255)
     */
    private $budget;

    /**
     * @var string
     *
     * @ORM\Column(name="delay", type="string", length=255)
     */
    private $delay;

    /**
     * @var string
     *
     * @ORM\Column(name="priority", type="string", length=255)
     */
    private $priority;

    /**
     * @var RiskStrategy|null
     *
     * @Serializer\Exclude()
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\RiskStrategy")
     * @ORM\JoinColumn(name="risk_strategy_id")
     */
    private $riskStrategy;

    /**
     * @var string
     *
     * @ORM\Column(name="measure", type="text")
     */
    private $measure;

    /**
     * @var RiskCategory|null
     *
     * @Serializer\Exclude()
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\RiskCategory")
     * @ORM\JoinColumn(name="risk_category_id")
     */
    private $riskCategory;

    /**
     * @var User|null
     *
     * @Serializer\Exclude()
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id")
     */
    private $responsibility;

    /**
     * @var \DateTime|null
     *
     * @Serializer\Type("DateTime<'Y-m-d H:i:s'>")
     *
     * @ORM\Column(name="due_date", type="date", nullable=true)
     */
    private $dueDate;

    /**
     * @var Status|null
     *
     * @Serializer\Exclude()
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Status")
     * @ORM\JoinColumn(name="status_id")
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @Serializer\Type("DateTime<'Y-m-d H:i:s'>")
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var \DateTime|null
     *
     * @Serializer\Type("DateTime<'Y-m-d H:i:s'>")
     * @Gedmo\Timestampable(on="update")
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * Risk constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

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
     * Set title.
     *
     * @param string $title
     *
     * @return Risk
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return Risk
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set cost.
     *
     * @param string $cost
     *
     * @return Risk
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Get cost.
     *
     * @return string
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set budget.
     *
     * @param string $budget
     *
     * @return Risk
     */
    public function setBudget($budget)
    {
        $this->budget = $budget;

        return $this;
    }

    /**
     * Get budget.
     *
     * @return string
     */
    public function getBudget()
    {
        return $this->budget;
    }

    /**
     * Set delay.
     *
     * @param string $delay
     *
     * @return Risk
     */
    public function setDelay($delay)
    {
        $this->delay = $delay;

        return $this;
    }

    /**
     * Get delay.
     *
     * @return string
     */
    public function getDelay()
    {
        return $this->delay;
    }

    /**
     * Set priority.
     *
     * @param string $priority
     *
     * @return Risk
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority.
     *
     * @return string
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set measure.
     *
     * @param string $measure
     *
     * @return Risk
     */
    public function setMeasure($measure)
    {
        $this->measure = $measure;

        return $this;
    }

    /**
     * Get measure.
     *
     * @return string
     */
    public function getMeasure()
    {
        return $this->measure;
    }

    /**
     * Set dueDate.
     *
     * @param \DateTime $dueDate
     *
     * @return Risk
     */
    public function setDueDate(\DateTime $dueDate = null)
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    /**
     * Get dueDate.
     *
     * @return \DateTime
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return Risk
     */
    public function setCreatedAt(\DateTime $createdAt)
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
     * @return Risk
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
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
     * Set impact.
     *
     * @param Impact $impact
     *
     * @return Risk
     */
    public function setImpact(Impact $impact = null)
    {
        $this->impact = $impact;

        return $this;
    }

    /**
     * Get impact.
     *
     * @return Impact
     */
    public function getImpact()
    {
        return $this->impact;
    }

    /**
     * Returns impact id.
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("impact")
     *
     * @return string
     */
    public function getImpactId()
    {
        return $this->impact ? $this->impact->getId() : null;
    }

    /**
     * Returns impact name.
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("impactName")
     *
     * @return string
     */
    public function getImpactName()
    {
        return $this->impact ? $this->impact->getName() : null;
    }

    /**
     * Set riskStrategy.
     *
     * @param RiskStrategy $riskStrategy
     *
     * @return Risk
     */
    public function setRiskStrategy(RiskStrategy $riskStrategy = null)
    {
        $this->riskStrategy = $riskStrategy;

        return $this;
    }

    /**
     * Get riskStrategy.
     *
     * @return RiskStrategy
     */
    public function getRiskStrategy()
    {
        return $this->riskStrategy;
    }

    /**
     * Returns riskStrategy id.
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("riskStrategy")
     *
     * @return string
     */
    public function getRiskStrategyId()
    {
        return $this->riskStrategy ? $this->riskStrategy->getId() : null;
    }

    /**
     * Returns riskStrategy name.
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("riskStrategyName")
     *
     * @return string
     */
    public function getRiskStrategyName()
    {
        return $this->riskStrategy ? $this->riskStrategy->getName() : null;
    }

    /**
     * Set riskCategory.
     *
     * @param RiskCategory $riskCategory
     *
     * @return Risk
     */
    public function setRiskCategory(RiskCategory $riskCategory = null)
    {
        $this->riskCategory = $riskCategory;

        return $this;
    }

    /**
     * Get riskCategory.
     *
     * @return RiskCategory
     */
    public function getRiskCategory()
    {
        return $this->riskCategory;
    }

    /**
     * Returns riskCategory id.
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("riskCategory")
     *
     * @return string
     */
    public function getRiskCategoryId()
    {
        return $this->riskCategory ? $this->riskCategory->getId() : null;
    }

    /**
     * Returns riskCategory name.
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("riskCategoryName")
     *
     * @return string
     */
    public function getRiskCategoryName()
    {
        return $this->riskCategory ? $this->riskCategory->getName() : null;
    }

    /**
     * Set responsibility.
     *
     * @param User $responsibility
     *
     * @return Risk
     */
    public function setResponsibility(User $responsibility = null)
    {
        $this->responsibility = $responsibility;

        return $this;
    }

    /**
     * Get responsibility.
     *
     * @return User
     */
    public function getResponsibility()
    {
        return $this->responsibility;
    }

    /**
     * Returns responsibility id.
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("responsibility")
     *
     * @return string
     */
    public function getResponsibilityId()
    {
        return $this->responsibility ? $this->responsibility->getId() : null;
    }

    /**
     * Returns responsibility full name.
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("responsibilityFullName")
     *
     * @return string
     */
    public function getResponsibilityFullName()
    {
        return $this->responsibility ? $this->responsibility->getFullName() : null;
    }

    /**
     * Set status.
     *
     * @param Status $status
     *
     * @return Risk
     */
    public function setStatus(Status $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Returns status id.
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("status")
     *
     * @return string
     */
    public function getStatusId()
    {
        return $this->status ? $this->status->getId() : null;
    }

    /**
     * Returns status name.
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("statusName")
     *
     * @return string
     */
    public function getStatusName()
    {
        return $this->status ? $this->status->getName() : null;
    }
}