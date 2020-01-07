<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity("login")
 */
class User implements UserInterface, \Serializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180)
     */
    private $login;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="boolean", options={"default" = false})
     */
    private $isActivate;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Email", mappedBy="user", orphanRemoval=true, cascade={"persist"})
     */
    private $emails;

    /**
     *
     */
    private $emailUser;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastUpdate;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\TokenResetPassword", cascade={"persist", "remove"})
     */
    private $tokenResetPassword;


    public function __construct()
    {
        $this->isActivate = false;
        $this->roles = ['ROLE_USER'];
        $this->emails = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->login;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): ?array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): ?string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getIsActivate(): ?bool
    {
        return $this->isActivate;
    }

    public function setIsActivate(bool $isActivate): self
    {
        $this->isActivate = $isActivate;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->login,
            $this->password,
            $this->roles,
            $this->isActivate
        ]);
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->login,
            $this->password,
            $this->roles,
            $this->isActivate
        ) = unserialize($serialized, ['allowed_classes' => false]);
    }

    /**
     * @return Collection|Email[]
     */
    public function getEmails(): Collection
    {
        return $this->emails;
    }

    public function addEmail(Email $email): self
    {
        if (!$this->emails->contains($email)) {
            $this->emails[] = $email;
            $email->setUser($this);
        }

        return $this;
    }

    public function removeEmail(Email $email): self
    {
        if ($this->emails->contains($email)) {
            $this->emails->removeElement($email);
            // set the owning side to null (unless already changed)
            if ($email->getUser() === $this) {
                $email->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmailUser()
    {
        return $this->emailUser;
    }

    /**
     * @param string $emailUser
     */
    public function setEmailUser($emailUser): self
    {

            $email = new Email();
            $email->setEmail($emailUser);
            $this->addEmail($email);

        $this->$emailUser = $emailUser;
        return $this;
    }

    public function getLastUpdate(): ?\DateTimeInterface
    {
        return $this->lastUpdate;
    }

    public function setLastUpdate(?\DateTimeInterface $lastUpdate): self
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }

    public function getTokenResetPassword(): ?TokenResetPassword
    {
        return $this->tokenResetPassword;
    }

    public function setTokenResetPassword(TokenResetPassword $tokenResetPassword): self
    {
        $this->tokenResetPassword = $tokenResetPassword;
        // set the owning side of the relation if necessary

        if ($tokenResetPassword->getUser() !== $this) {
            $tokenResetPassword->setUser($this->getId());
        }



        $tokenResetPassword->setUser($this->getId());
        return $this;
    }



}
