<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TaskRepository::class)
 */
class Task
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $task_name;

    /**
     * @ORM\Column(type="text")
     */
    private $task_description;

    /**
     * @ORM\Column(type="integer")
     */
    private $add_date;

    /**
     * @ORM\Column(type="integer")
     */
    private $completed_date;

    /**
     * @ORM\Column(type="integer")
     */
    private $status_id;

    /**
     * @ORM\ManyToOne(targetEntity=Status::class, inversedBy="tasks")
     */
    private $status;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTaskName(): ?string
    {
        return $this->task_name;
    }

    public function setTaskName(string $task_name): self
    {
        $this->task_name = $task_name;

        return $this;
    }

    public function getTaskDescription(): ?string
    {
        return $this->task_description;
    }

    public function setTaskDescription(string $task_description): self
    {
        $this->task_description = $task_description;

        return $this;
    }

    public function getAddDate(): ?int
    {
        return $this->add_date;
    }

    public function setAddDate(int $add_date): self
    {
        $this->add_date = $add_date;

        return $this;
    }

    public function getCompletedDate(): ?int
    {
        return $this->completed_date;
    }

    public function setCompletedDate(int $completed_date): self
    {
        $this->completed_date = $completed_date;

        return $this;
    }

    public function getStatusId(): ?int
    {
        return $this->status_id;
    }

    public function setStatusId(int $status_id): self
    {
        $this->status_id = $status_id;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): self
    {
        $this->status = $status;

        return $this;
    }
}
