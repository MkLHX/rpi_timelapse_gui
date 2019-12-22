<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FTPTransfertRepository")
 */
class FTPTransfert
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank
     * @Assert\Regex(
     *      pattern="/^([a-z.-]*|(0-9)|((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))$/",
     *      match=true,
     *      message="Host can be domain or ipv4 ftp.domain.com or 192.168.0.12"
     * )
     * @ORM\Column(type="string", length=255)
     */
    private $host;

    /**
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     */
    private $login;

    /**
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @Assert\NotBlank
     * @Assert\Regex(
     *      pattern="/^(\/[a-zA-Z0-9_]([a-zA-Z0-9-_ ]*))*$/",
     *      match=true,
     *      message="Path should looks /ftp_folder/timelapse or /ftp-folder/_timelapse"
     * )
     * @ORM\Column(type="string", length=255)
     */
    private $path;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

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

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }
}
