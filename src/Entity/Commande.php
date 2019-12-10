<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommandeRepository")
 */
class Commande
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;



    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     */
    private $orderDate;

    /**
     * @ORM\Column(type="float")
     */
    private $orderTotal;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Client", inversedBy="commandes")
     */
    private $client;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Details", mappedBy="commandes")
     */
    private $details;

    /**
     * @ORM\Column(type="string", length=180)
     */
    private $invoiceId;

    public function __construct()
    {
        $this->details = new ArrayCollection();
    }





    public function getId(): ?int
    {
        return $this->id;
    }


    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getOrderDate(): ?\DateTimeInterface
    {
        return $this->orderDate;
    }

    public function setOrderDate(\DateTimeInterface $orderDate): self
    {
        $this->orderDate = $orderDate;

        return $this;
    }

    public function getOrderTotal(): ?float
    {
        return $this->orderTotal;
    }

    public function setOrderTotal(float $orderTotal): self
    {
        $this->orderTotal = $orderTotal;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Collection|Details[]
     */
    public function getDetails(): Collection
    {
        return $this->details;
    }

    public function addDetail(Details $detail): self
    {
        if (!$this->details->contains($detail)) {
            $this->details[] = $detail;
            $detail->setCommandes($this);
        }

        return $this;
    }

    public function removeDetail(Details $detail): self
    {
        if ($this->details->contains($detail)) {
            $this->details->removeElement($detail);
            // set the owning side to null (unless already changed)
            if ($detail->getCommandes() === $this) {
                $detail->setCommandes(null);
            }
        }

        return $this;
    }

    public function getInvoiceId(): ?string
    {
        return $this->invoiceId;
    }

    public function setInvoiceId(?string $invoiceId): self
    {
        $this->invoiceId = $invoiceId;

        return $this;
    }



}
