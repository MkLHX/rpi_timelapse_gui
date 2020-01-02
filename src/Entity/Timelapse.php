<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TimelapseRepository")
 */
class Timelapse
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     */
    private $resolution;


    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fileExtension;

    /**
     * @Assert\NotBlank
     * @Assert\Regex(
     *      pattern="/^(\d{1,2} ){1,2}(\* ){2,3}|((\*\/\d{1,2} \d{1,2} |\*\/\d{1,2} |(\d{1,2} ){2})(\* ){3,4})|(?:\* ){4,5}|\@(reboot|weekly|yearly|annually|monthly|daily|hourly)$/",
     *      match=true,
     *      message="Schedule value expect format like 25/* * * * *"
     * )
     * @ORM\Column(type="string")
     */
    private $schedule;

    /**
     * @Assert\NotBlank
     * @Assert\Regex(
     *      pattern="/^([a-zA-Z0-9_]([a-zA-Z0-9-_ ]\/*))*$/",
     *      match=true,
     *      message="Path should looks /my/timelapse/folder or /my-timelapse/_folder"
     * )
     * @ORM\Column(type="string", length=255)
     */
    private $path;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getResolution(): ?string
    {
        return $this->resolution;
    }

    public function setResolution(string $resolution): self
    {
        $this->resolution = $resolution;

        return $this;
    }

    public function getSchedule(): ?String
    {
        return $this->schedule;
    }

    public function setSchedule(String $schedule): self
    {
        $this->schedule = $schedule;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getFileExtension(): ?string
    {
        return $this->fileExtension;
    }

    public function setFileExtension(?string $fileExtension): self
    {
        $this->fileExtension = $fileExtension;

        return $this;
    }
}
